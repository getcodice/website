'use strict';

var gulp    = require('gulp');
var gutil   = require('gulp-util');
var plugins = {
    concat: require('gulp-concat'),
    csso: require('gulp-csso'),
    sass: require('gulp-sass'),
};

gulp.task('scripts', function () {
    return gulp.src([
        './node_modules/jquery/dist/jquery.min.js',
        './node_modules/bootstrap-sass/assets/javascripts/bootstrap.min.js',
        './resources/scripts/prism.js',
        './resources/scripts/codice.js',
        './resources/scripts/demo.js',
    ]).pipe(plugins.concat('docs.js'))
      .pipe(gulp.dest('./public/assets/js'));
});

gulp.task('styles', function () {
    return gulp.src('./resources/styles/style.scss')
        .pipe(plugins.sass({
            includePaths: [
                './node_modules/bootstrap-sass/assets/stylesheets/',
            ],
        }))
        .on('error', onError)
        .pipe(plugins.csso())
        .pipe(gulp.dest('./public/assets/css'));
});

gulp.task('icons', function() {
    return gulp.src('./node_modules/font-awesome/fonts/**.*')
        .pipe(gulp.dest('./public/assets/fonts'));
});

gulp.task('assets', ['scripts', 'styles', 'icons']);

function onError(error) {
    gutil.log(gutil.colors.red('Error:'), error.toString());
    this.emit('end');
}
