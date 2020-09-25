<?php
declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Popup class
 */
class Popup extends Module
{   
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->name                     = 'popup';
        $this->tab                      = 'dashboard';
        $this->version                  = '1.0.0';
        $this->author                   = 'Marcin Puszka';
        $this->need_instance            = 0;
        $this->bootstrap                = true;
        $this->displayName              = $this->l('Popup module');
        $this->description              = $this->l('Simple module to show up popup');
        $this->confirmUninstall         = $this->l('Are you sure you want to uninstall?');
        $this->ps_versions_compliancy   = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];
        
        if (!Configuration::get('popup')) 
        {
            $this->warning = $this->l('No name provided');
        }

        parent::__construct();
    }

    /**
     * Install method
     *
     * @return boolean
     */
    public function install(): boolean
    {
        if (Shop::isFeatureActive()) 
        {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (
            !parent::install() ||
            !$this->registerHook('header') ||
            !Configuration::updateValue('popup', 'popup')
        ) {
            return false;
        }

        return parent::install() &&
            $this->registerHook('header') &&
            Configuration::updateValue('popup', 'popup');
    }

    /**
     * Uninstall method
     *
     * @return boolean
     */
    public function uninstall(): boolean
    {
        if (
            !parent::uninstall() ||
            !Configuration::deleteByName('popup')
        ) {
            return false;
        }

        return true;
    }

    /**
     * Undocumented function
     *
     * @param array $params
     * @return string|null
     */
    public function hookHeader(array $params): ?string
    {   
        $this->context->smarty->assign([
                'title'     => Configuration::get('title'),
                'text'      => Configuration::get('text'),
                'bgColor'   => Configuration::get('bg-color'),
                'startDate' => Configuration::get('start-date'),
                'endDate'   => Configuration::get('end-date')
        ]);
        
        $selectedProducts = (Configuration::get('selected')) ? explode(',', Configuration::get('selected')) : Configuration::get('selected');

        if ($selectedProducts && !empty($selectedProducts))
        {       
            if (
                isset($_GET['id_product']) && 
                !empty($_GET['id_product'])
            ) {
                $productId = $_GET['id_product'];

                if (in_array($productId, $selectedProducts)) 
                {
                    $this->context->controller->addCSS($this->_path . '/public/css/popup.css', 'all');
                    $this->context->controller->addJS($this->_path . '/public/js/all-min.js');

                    return $this->display(__FILE__, 'popup.tpl');
                }
            }
        }

        return null;
    }
    
    /**
     * Get content method
     *
     * @return string
     */
    public function getContent(): string
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name)) {
            $title              = strval(Tools::getValue('title'));
            $text               = strval(Tools::getValue('text'));
            $bgColor            = strval(Tools::getValue('bg-color'));
            $startDate          = strval(Tools::getValue('start-date'));
            $endDate            = strval(Tools::getValue('end-date'));
            $selectedProducts   = Tools::getValue('selected');

            if (
                !$title ||
                empty($title) ||
                !$text ||
                empty($text) ||
                !$bgColor ||
                empty($bgColor) ||
                !$startDate ||
                empty($startDate) ||
                !$endDate ||
                empty($endDate) ||
                !$selectedProducts ||
                empty($selectedProducts)
            ) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('title', $title);
                Configuration::updateValue('text', $text);
                Configuration::updateValue('bg-color', $bgColor);
                Configuration::updateValue('start-date', $startDate);
                Configuration::updateValue('end-date', $endDate);
                Configuration::updateValue('selected', implode(',', $selectedProducts));
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        return $output.$this->displayForm();
    }

    /**
     * Display form method
     *
     * @return string
     */
    public function displayForm(): string
    {
        
        $id_lang        = (int)Context::getContext()->language->id;
        $start          = 0;
        $limit          = 100;
        $order_by       = 'id_product';
        $order_way      = 'DESC';
        $id_category    = false; 
        $only_active    = true;
        $context        = null;

        $all_products=Product::getProducts(
            $id_lang, 
            $start, 
            $limit, 
            $order_by, 
            $order_way, 
            $id_category,
            $only_active, 
            $context
        );

        $optionsForSelect = [];
        foreach($all_products as $product) {
            $optionsForSelect[] = [
                'id'    => $product['id_product'],
                'name'  => $product['name']
            ];
        }

        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
            ],
            'input' => [
                [
                    'type'      => 'text',
                    'label'     => $this->l('Title'),
                    'name'      => 'title',
                    'size'      => 120,
                    'required'  => true
                ],
                [
                    'type'      => 'textarea',
                    'label'     => $this->l('Text'),
                    'name'      => 'text',
                    'required'  => true
                ],
                [
                    'type'      => 'color',
                    'label'     => $this->l('Background color'),
                    'name'      => 'bg-color',
                    'required'  => true,
                    'value'     => '#ffffff'
                ],
                [
                    'type'      => 'date',
                    'label'     => $this->l('Start date'),
                    'name'      => 'start-date',
                    'required'  => true
                ],
                [
                    'type'      => 'date',
                    'label'     => $this->l('End date'),
                    'name'      => 'end-date',
                    'required'  => true
                ],
                [
                    'type'      => 'select',
                    'label'     => $this->l('Products'),
                    'name'      => 'selected[]',
                    'required'  => true,
                    'multiple'  => true,
                    'options'   => [
                        'query' =>  $optionsForSelect,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                    
                ]
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();

        $helper->module             = $this;
        $helper->name_controller    = $this->name;
        $helper->token              = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex       = AdminController::$currentIndex.'&configure='.$this->name;

        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        $helper->default_form_language      = $defaultLang;
        $helper->allow_employee_form_lang   = $defaultLang;

        $helper->title          = $this->displayName;
        $helper->show_toolbar   = true;        
        $helper->toolbar_scroll = true;    
        $helper->submit_action  = 'submit'.$this->name;
        $helper->toolbar_btn    = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];

        $helper->fields_value['title']      = Tools::getValue('title', Configuration::get('title'));
        $helper->fields_value['text']       = Tools::getValue('text', Configuration::get('text'));
        $helper->fields_value['bg-color']   = Tools::getValue('bg-color', Configuration::get('bg-color'));
        $helper->fields_value['start-date'] = Tools::getValue('start-date', Configuration::get('start-date'));
        $helper->fields_value['end-date']   = Tools::getValue('end-date', Configuration::get('end-date'));

        $selectedProducts = (Configuration::get('selected')) ? explode(',', Configuration::get('selected')) : Configuration::get('selected');
        $helper->fields_value['selected[]'] = Tools::getValue('products', $selectedProducts);

        return $helper->generateForm($fieldsForm);
    }
}