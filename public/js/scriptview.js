var vm = new Vue({
    el: "#script_preview",
    data: {
        annotator: ''
    },
    ready: function() {
        this.annotator = $('#script_content').annotator();
        this.annotator.annotator("addPlugin", "Touch");
        this.annotator.annotator("addPlugin", "Store", {
            prefix: 'insertSaveCommentsURLHere'
        });
        // Sample Storage:
        // content.annotator('addPlugin', 'Store', {
        //     // The endpoint of the store on your server.
        //     prefix: '/store/endpoint',
        //
        //     // Attach the uri of the current page to all annotations to allow search.
        //     annotationData: {
        //         'uri': 'http://this/document/only'
        //     },
        //
        //     // This will perform a "search" action when the plugin loads. Will
        //     // request the last 20 annotations for the current url.
        //     // eg. /store/endpoint/search?limit=20&uri=http://this/document/only
        //     loadFromSearch: {
        //         'limit': 20,
        //         'uri': 'http://this/document/only'
        //     }
        // });

        // $('#content').annotator('addPlugin', 'Store', {
        //     urls: {
        //         // These are the default URLs.
        //         create:  '/annotations',
        //         update:  '/annotations/:id',
        //         destroy: '/annotations/:id',
        //         search:  '/search'
        //     }
        // });
    },
    methods: {
    }
})