module.exports = function(grunt) { 

    var requirejs   = grunt.config('requirejs') || {};
    var clean       = grunt.config('clean') || {};
    var copy        = grunt.config('copy') || {};

    var root        = grunt.option('root');
    var libs        = grunt.option('mainlibs');
    var ext         = require(root + '/tao/views/build/tasks/helpers/extensions')(grunt, root);

    /**
     * Remove bundled and bundling files
     */
    clean.taoopenwebitembundle = ['output',  root + '/taoOpenWebItem/views/js/controllers.min.js'];
    
    /**
     * Compile tao files into a bundle 
     */
    requirejs.taoopenwebitembundle = {
        options: {
            baseUrl : '../js',
            dir : 'output',
            mainConfigFile : './config/requirejs.build.js',
            paths : { 'taoOpenWebItem' : root + '/taoOpenWebItem/views/js' },
            modules : [{
                name: 'taoOpenWebItem/controller/routes',
                include : ext.getExtensionsControllers(['taoOpenWebItem']),
                exclude : ['mathJax', 'mediaElement'].concat(libs)
            }]
        }
    };

    /**
     * copy the bundles to the right place
     */
    copy.taoopenwebitembundle = {
        files: [
            { src: ['output/taoOpenWebItem/controller/routes.js'],  dest: root + '/taoOpenWebItem/views/js/controllers.min.js' },
            { src: ['output/taoOpenWebItem/controller/routes.js.map'],  dest: root + '/taoOpenWebItem/views/js/controllers.min.js.map' }
        ]
    };

    grunt.config('clean', clean);
    grunt.config('requirejs', requirejs);
    grunt.config('copy', copy);

    // bundle task
    grunt.registerTask('taoopenwebitembundle', ['clean:taoopenwebitembundle', 'requirejs:taoopenwebitembundle', 'copy:taoopenwebitembundle']);
};
