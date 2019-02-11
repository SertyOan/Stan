import Class from 'oyat/Class';
import Application from '../Application';

export default Class.extend({
    __construct: function(view, event) {
        view.on('Render', function() {
            view.build(event);
        });

        view.on('Attend', function(status) {
            var params = {
                eventID: event.id,
                status: status
            };

            Application.callAPI({
                method: 'Events::attend',
                params: params,
                onSuccess: function(eventUpdated) {
                    view.build(eventUpdated);
                }
            });
        });

        view.on('HeadClick', function() {
            if(view.focused) {
                view.unfocus();
                Application.bus.emit('UnfocusEvent', event);
            }
            else {
                Application.bus.emit('FocusEvent', event);
            }
        });

        Application.bus.on('FocusEvent', function(busEvent) {
            if(busEvent.id === event.id) {
                view.focus();
            }
            else {
                view.unfocus();
            }
        });
    }
});
