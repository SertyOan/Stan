import TextField from 'oyat/UI/TextField';

export default TextField.extend({
    __construct: function(options) {
        this.__parent(options);

        this.on('KeyPress', function(data) {
            var key = parseInt(data.browserEvent.key, 10);

            if(!(key !== NaN && key >= 0 && key <= 9)) {
                data.browserEvent.preventDefault();
                data.browserEvent.stopPropagation();
            }
        });
    }
});
