/**
* @Author: Kongho
* @Date:   2017-02-06 14:24:04
* @Email:  kongho@3ncto.com
* @Filename: gulpfile.js
* @Last modified by:   Kongho
* @Last modified time: 2017-02-16 10:18:21
* @Copyright: 3NCTO Co., Ltd.
*/



/*jshint node: true*/
//引入gulp及各种组件;
var gulp =                   require('gulp'),
    less =                   require('gulp-less'),
    clean =                  require('gulp-clean'),
    ngmin =                  require('gulp-ngmin'),
    path =                   require('path');
    rename =                 require("gulp-rename");
    concat =                 require('gulp-concat'),
    uglify =                 require('gulp-uglify'),
    source =                 require('vinyl-source-stream'),
    buffer =                 require('vinyl-buffer'),
    imagemin =               require('gulp-imagemin'),
    minifyCSS =              require('gulp-minify-css'),
    ngAnnotate =             require('gulp-ng-annotate'),    
    browserify =             require("browserify"),
    sourcemaps =             require("gulp-sourcemaps"),
    stripDebug =             require('gulp-strip-debug'),
    browserSync =            require('browser-sync').create(), 
    LessPluginCleanCSS = require('less-plugin-clean-css'),
    LessPluginAutoPrefix = require('less-plugin-autoprefix'),
    cleancss     = new LessPluginCleanCSS({ advanced: true }),
    autoprefix   = new LessPluginAutoPrefix({ browsers: ["last 2 versions"] }),   
    imageminOptipng =        require('imagemin-optipng'),
    imageminJpegRecompress = require('imagemin-jpeg-recompress'),
    gulpSequence = require('gulp-sequence'),
    watchify = require('watchify'),
    gulpif = require('gulp-if');

//设置各种输入输出文件夹的位置;
var srcScript = ['src/js/controllers/**/*.js','src/js/model/**/*.js'],
    srcCss = 'src/css/less/*.css',
    srcSass = 'src/css/*.scss',
    srcLess = 'src/**/*.less',
    srcImage = 'src/img/*.*',
    srcHtml = 'src/**/*.html',
    srcTpl = 'src/tpl/**/*.html',
    srcBundle = ['src/js/vendor.js','src/js/*.js','src/js/directives/**/*.js','src/js/services/**/*.js'],

    dst = 'dist',
    dstScript = 'dist/js',
    dstCSS = 'dist/css',
    dstSass = 'dist/css',
    dstImage = 'dist/img',
    dstHtml = 'dist',
    dstTpl = 'dist/tpl';

//初始化browserify
var b = browserify({
    entries: "./src/vendor/vendor.js",
    debug: true
});
var isRev = true;

function bundle() {
    var jsLibName = 'vendor.js';

    return b.bundle()
        .pipe(source(jsLibName))
        .pipe(buffer())
        .pipe(gulp.dest('./dist/vendor').on('end', function() {  //打包js后继续进行一些后续操作
          gulp.src(['./dist/vendor/' + jsLibName,'src/js/*.js','src/js/directives/**/*.js','src/js/services/**/*.js'])
          .pipe(concat('vendor.min.js'))
          .pipe(gulpif(isRev,uglify({outSourceMap: true})))
          .pipe(gulpif(isRev,stripDebug()))
          .pipe(gulp.dest('./dist/vendor'))
        }));
}
//打包js
gulp.task('build-all-js', bundle);

//启动watchify监测文件改动
gulp.task('watch-js', function() {
  b.plugin(watchify);  //设置watchify插件
  b.on('update', function(ids) {  //监测文件改动
    ids.forEach(function(v) {
      console.log('bundle changed file:' + v);  //记录改动的文件名
    });

    gulp.start('build-all-js');  //触发打包js任务
  });

  return bundle();  //须要先执行一次bundle
});

//执行默认命令不用rev
gulp.task('judge', function(){
  isRev = false;
  return gulp.src('rev')
      .pipe(clean());
})

//处理JS文件:压缩,然后换个名输出;
//命令行使用gulp script启用此任务;
gulp.task('script', function() {
    return gulp.src(srcScript,{base:'src'})
        .pipe(ngAnnotate())
        .pipe(ngmin({dynamic: false}))
        .pipe(uglify({outSourceMap: true}))
        .pipe(gulpif(isRev,uglify({outSourceMap: true})))
        .pipe(gulpif(isRev,stripDebug()))
        .pipe(gulp.dest(dst));
});

//less文件输出css;
//命令行使用gulp less启用此任务;
gulp.task('less', function () {
  return gulp.src('src/css/less/app.less')
    .pipe(sourcemaps.init())
    .pipe(less({
      paths: [ path.join(__dirname, 'less', 'includes') ],
      plugins: [autoprefix, cleancss]
    }))
    .pipe(sourcemaps.write('.'))
    .pipe(minifyCSS())
    // .pipe(gulp.dest('src/css'))
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest('dist/css'))
});

//图片压缩任务,支持JPEG、PNG及GIF文件;
//命令行使gulp jpgmin启用此任务;
gulp.task('imgmin', function() {
    var jpgmin = imageminJpegRecompress({
            accurate: true,//高精度模式
            quality: "high",//图像质量:low, medium, high and veryhigh;
            method: "smallfry",//网格优化:mpe, ssim, ms-ssim and smallfry;
            min: 70,//最低质量
            loops: 0,//循环尝试次数, 默认为6;
            progressive: false,//基线优化
            subsample: "default"//子采样:default, disable;
        }),
        pngmin = imageminOptipng({
            optimizationLevel: 4
        });
    gulp.src(srcImage)
        .pipe(imagemin({
            use: [jpgmin, pngmin]
        }))
        .pipe(gulp.dest(dstImage));
});

// 将ueditor相关文件copy到dist目录
gulp.task('lib',function(){
  gulp.src('src/lib/**/*')
    .pipe(gulp.dest('dist/lib'));
});



//把所有html页面扔进dist文件夹(不作处理);
//命令行使用gulp html启用此任务;
gulp.task('html', function() {
    gulp.src(srcHtml)
        .pipe(gulp.dest(dstHtml));
});

gulp.task('fonts',function(){
    gulp.src('src/fonts/**/*.*',{base:'src'})
        .pipe(gulp.dest('dist'))
});


//服务器任务:以dist文件夹为基础,启动服务器;
//命令行使用gulp server启用此任务;
gulp.task('server', function() {
    browserSync.init({
        server: "dist"
    });
});

//删除
gulp.task("clean", function(){
    return gulp.src('dist')
        .pipe(clean());
})

//监控改动并自动刷新任务;
//命令行使用gulp auto启动;
gulp.task('auto', function() {
    gulp.watch(srcScript, ['script']);
    gulp.watch(srcLess, ['less']);
    gulp.watch(srcCss, ['less']).on('change', browserSync.reload);
    gulp.watch(srcImage, ['imgmin']);
    gulp.watch(srcHtml, ['html']);
    gulp.watch(srcBundle,['watch-js']);
    gulp.watch('dist/**/*.*').on('change', browserSync.reload);
});


//gulp默认任务(集体走一遍,然后开监控);
gulp.task('default',gulpSequence('clean', 'judge', ['script', 'build-all-js', 'less', 'imgmin', 'html', 'fonts'], 'server', 'auto'));

//gulp build合并压缩文件;
gulp.task('build', gulpSequence('clean', 'script', 'build-all-js', 'less', 'imgmin', 'html', 'fonts'));
