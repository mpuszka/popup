var gulp      = require('gulp');
var sass      = require('gulp-sass');
var cleanCSS  = require('gulp-clean-css');
var minify    = require('gulp-minify');
var concat    = require('gulp-concat');

gulp.task('sass', async () => {
    gulp.src('./views/scss/popup.scss')
    .pipe(sass()) 
    .pipe(cleanCSS({compatibility: 'ie8'}))
    .pipe(gulp.dest('./public/css'))
});

gulp.task('compress', async () => {
  gulp.src(
      [
        './views/js/*.js'
      ]
    )
    .pipe(concat('all.js'))
    .pipe(minify())
    .pipe(gulp.dest('./public/js'))
});

gulp.task('default', gulp.parallel('sass', 'compress'));