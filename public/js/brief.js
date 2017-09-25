function Spot(id, type, adLength, startDate, endDate, comment) {
    this.id = id;
    this.type = type === undefined ? Spot.TYPE_NONE : type;
    this.adLength = adLength === undefined ? 0 : adLength;
    this.startDate = startDate;
    this.endDate = endDate;
    this.comment = comment === undefined ? 'comment' : comment;
}

Object.defineProperty(Spot, "TYPE_NONE", { value: null });
Object.defineProperty(Spot, "TYPE_RECORDED", { value: 'recorded' });
Object.defineProperty(Spot, "TYPE_LIVE", { value: 'live' });

Vue.component('spot', {
    template: '#spot-template',
    props: ['spot'],
    methods: {
        removeSpot: function(id) {
            var length = vm.spots.length;
            for(var i = 0; i < length; i++) {
                if(vm.spots[i].id == id) {
                    vm.spots.splice(i, 1);
                    return;
                }
            }
        }
    }
});

var vm = new Vue({
    el: "#brief",
    data: {
        spotCount: 1,
        spots: [
            new Spot(1)
        ],
        instructions: '',
        voices: '',
        message: '',
        sellingPoint: '',
        specialInstructions: '',
        purchasingBarrier: '',
        customerToDo: '',
        explain: ''
    },
    ready: function() {
        this.setupDatePicker();
    },
    methods: {
        addSpot: function() {
            this.spotCount++;
            var spot = new Spot(this.spotCount);

            this.spots.push(
                spot
            );

            //Next tick runs the next time the DOM is updated so we use that to setup datepicker only once the new spot is rendered
            var that = this;
            Vue.nextTick(function() {
                that.setupDatePicker();
            })
        },
        setupDatePicker: function() {
            $('#spot-'+this.spotCount+' .start-date').datepicker({
                autoclose: true,
                format: 'dd-mm-yyyy'
            });
            $('#spot-'+this.spotCount+' .end-date').datepicker({
                autoclose: true,
                format: 'dd-mm-yyyy'
            });
        }
    }
});