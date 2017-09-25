var Audio = function (url, downloadName) {
    this.url = url;
    this.downloadName = downloadName;
}

var Script = function(id, name, scriptContent, start, end, broadcastType, voiceGender, voiceType, considerations, audioRecording, adKey, headline, renaming) {
    
    if(id === undefined) {
        console.log('ID passed into script is undefined');
        return;
    }
    
    this.id = id;
    this.name = name === undefined ? ('Script #' + id) : name;
    this.scriptContent = scriptContent;
    this.start = start;
    this.end = end;
    this.broadcastType = broadcastType === undefined ? 'none' : broadcastType;
    this.voiceGender = voiceGender === undefined ? 'none' : voiceGender;
    this.voiceType = voiceType === undefined ? '' : voiceType;
    this.considerations = considerations;
    this.audioRecording = audioRecording === undefined ? '' : audioRecording;
    this.adKey = adKey;
    this.headline = headline;
    this.isActive = false;
    this.recorded = false;
    this.renaming = false;
    
}

// WYSIWYG text editor to edit scripts
// tinymce directive
// http://forum.vuejs.org/topic/1879/tinymce-directive/2
Vue.directive('tinymce-editor',{
    twoWay: true,
    bind: function() {
        var self = this;
        tinymce.init({
            selector: '#script_content',
            removed_menuitems: 'newdocument',
            menubar: false,
            init_instance_callback: function(editor) {
                vm.resizeEditor();

                // init tinymce
                editor.on('init', function() {
                    tinymce.get('script_content').setContent(self.value);
                });

                // when typing keyup event
                editor.on('keyup', function() {

                    // get new value
                    var new_value = tinymce.get('script_content').getContent(self.value);

                    // set model value
                    self.set(new_value)
                });
            },
            save_onsavecallback: function() {
                $('.saveProgress').show().html('Save success. Script has been saved').css('color', 'green');
                setTimeout(function() { $('.saveProgress').fadeOut(); }, 4000);
            },
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code',
                'save'
            ],
            toolbar: 'insertfile save undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
            content_css: [
                '//fast.fonts.net/cssapi/e6dc9b99-64fe-4292-ad98-6974f93cd2a2.css',
                '//www.tinymce.com/css/codepen.min.css'
            ]
        });
    },
    update: function(newVal, oldVal) {
        // set val and trigger event
        $(this.el).val(newVal).trigger('keyup');
    }

});

var vm = new Vue({
    el: "#script",
    data: {
        //Audio
        audio_context: '',
        recorder: '',
        isRecording: false,
        recordLength: 0,
        
        //UI
        formHidden: false,
        
        //Script stuff
        scripts: [],
        scriptCount: 1,
        currentScriptIndex: 0,
        
        //Global fields
        client: '',
        campaign: '',
        salesRep: '',
        producer: ''
    },

    ready: function() {
        removeOptionFromSelect($('#content_content_type_id'), ContentTypeIDOfDailyLog);

        //---Audio recording
        try {
            // webkit shim
            window.AudioContext = window.AudioContext || window.webkitAudioContext;
            navigator.getUserMedia =  navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || navigator.oGetUserMedia;;

            window.URL = window.URL || window.webkitURL;

            this.audio_context = new AudioContext;
            console.log('Audio context set up.');
            console.log('navigator.getUserMedia ' + (navigator.getUserMedia ? 'available.' : 'not present!'));
        } catch (e) {
            console.log('No web audio support in this browser!');
        }

        var that = this;
        navigator.getUserMedia({audio: true},
            function(stream) {
                var input = that.audio_context.createMediaStreamSource(stream);
                console.log('Media stream created.');

                that.recorder = new Recorder(input);
                console.log('Recorder initialised.');
            },
            function(e) {
                console.log('No live audio input: ' + e);
            }
        );

        //---UI stuff
        this.updateRecordTime();

        this.resizeSidebar();
        
        //---Scripts
        var script = new Script(1);
        script.isActive = true;

        this.scripts.push(script);
        
    },

    methods: {
        //---Audio recording stuff
        record: function() {
            //Start
            if(!this.isRecording) {
                if(this.scripts[this.currentScriptIndex].recorded) {
                    var that = this;
                    bootbox.confirm("Are you sure you replace the current recording?", function(result){
                        if (result) {
                            that.recordLength = 0;
                            that.isRecording = true;
                            that.recorder && that.recorder.record();
                        }
                    })
                } else {
                    this.recordLength = 0;
                    this.isRecording = true;
                    this.recorder && this.recorder.record();
                }
            }
            //Stop
            else {
                this.isRecording = false;
                this.recorder && this.recorder.stop();
                console.log('Stopped recording.');

                // create WAV download link using audio data blob
                this.createDownloadLink();

                this.recorder.clear();

                this.scripts[this.currentScriptIndex].recorded = true;
            }
        },
        updateRecordTime: function() {
            this.recordLength++;
            var that = this;
            setTimeout(function() {
                that.updateRecordTime();
            }, 1000);
        },
        createDownloadLink: function() {
            var that = this;
            this.recorder && this.recorder.exportWAV(function(blob) {
                var url = URL.createObjectURL(blob);
                // var au = document.getElementById('demo_recording');//document.createElement('audio');
                // var hf = document.getElementById('demo_download_link');

                // au.controls = true;
                // au.src = url;
                // hf.href = url;
                // hf.download = new Date().toISOString() + '.wav';
                // hf.innerHTML = hf.download;
                
                that.scripts[that.currentScriptIndex].audioRecording = new Audio(url, new Date().toISOString() + '.wav')
            });
        },

        //---Form and editor stuff
        hideForm: function() {
            var that = this;
            if(!this.formHidden) {
                $('#script_form').slideUp("fast", function() {that.resizeEditor()});
                this.formHidden = true;
                this.resizeEditor();
            }
            else {
                $('#script_form').slideDown("fast", function() {that.resizeEditor()});
                this.formHidden = false;
            }
        },
        resizeEditor: function () {
            var height = (window.innerHeight-(this.formHidden ? 0 : document.getElementById("script_form").offsetHeight) - 330);
            height = height > 250 ? height : 250;
            document.getElementById('script_content' + '_ifr').style.height= height + 'px';
        },
        resizeSidebar: function () {
            height = (window.innerHeight-120);
            document.getElementById('sidebar_container').style.height= height + 'px';
        },
        
        //---Adding, changing and removing scripts
        addScript: function() {
            var newScript = new Script(++this.scriptCount);
            this.scripts.push(newScript);
        },
        removeScript: function(id) {
            var that = this;
            bootbox.confirm("Are you sure you want to delete this script?", function(result){

                if (result) {

                    var length = vm.scripts.length;
                    var previousTab = 0;
                    for(var i = 0; i < length; i++) {
                        if(vm.scripts[i].id == id) {
                            vm.scripts.splice(i, 1);
                            break;
                        }
                        previousTab = vm.scripts[i].id;
                    }
                    that.changeScript(previousTab);

                }

            })
        },
        changeScript: function(id, scriptTab) {
            var length = vm.scripts.length;
            //If we click the same tab twice, then set to renaming script mode
            if(vm.scripts[vm.currentScriptIndex].id == id) {
                vm.scripts[vm.currentScriptIndex].renaming = true;
                Vue.nextTick(function() {
                    $('.rename-script-field').focus();
                });
                return;
            }
            vm.scripts[vm.currentScriptIndex].renaming = false;
            for(var i = 0; i < length; i++) {
                if(vm.scripts[i].id == id) {
                    vm.scripts[i].isActive = true;
                    vm.currentScriptIndex = i;
                    tinymce.get('script_content').setContent(vm.scripts[i].scriptContent ? vm.scripts[i].scriptContent : '');
                }
                else {
                    vm.scripts[i].isActive = false;
                }
            }
        },
        renameScript: function() {
            vm.scripts[vm.currentScriptIndex].renaming = false;
        },
        
        //---Utility
        formatSeconds: function(time) {

            // Hours, minutes and seconds
            var hrs = ~~(time / 3600);
            var mins = ~~((time % 3600) / 60);
            var secs = time % 60;

            // Output like "1:01" or "4:03:59" or "123:03:59"
            var ret = "";

            if (hrs > 0) {
                ret += "" + hrs + ":" + (mins < 10 ? "0" : "");
            }

            ret += "" + mins + ":" + (secs < 10 ? "0" : "");
            ret += "" + secs;
            return ret;
        }
    }
})

window.onresize = function() {
    vm.resizeEditor();
    vm.resizeSidebar();
}
