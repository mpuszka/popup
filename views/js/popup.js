jQuery(document).ready(($) => {
    let popup       = $('.overlay');
    let close       = $('.overlay .close');
    let startDate   = popup.data('start-date');
    let endDate     = popup.data('end-date');
    let today       = formatDate(new Date());

    close.on('click', () => {
        popup.css('opacity', 0);
        popup.css('visibility', 'hidden');
    });

    if (startDate <= today && today <= endDate) {
        $('body').mouseleave(() => {
            popup.css('visibility', 'visible');
            popup.css('opacity', 1);
        }); 
    }
});

let formatDate = (date) => {
    let d       = new Date(date),
        month   = '' + (d.getMonth() + 1),
        day     = '' + d.getDate(),
        year    = d.getFullYear();

    if (month.length < 2) {
        month = '0' + month;
    }
        
    if (day.length < 2) {
        day = '0' + day;
    }
        
    return [year, month, day].join('-');
}