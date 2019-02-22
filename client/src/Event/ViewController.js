import Class from 'oyat/Class';
import Application from '../Application';

export default Class.extend({
    __construct: function(view, event) {
        this.view = view;

        view.on('Render', function() {
            this.refresh(event);
        }.bind(this));

        view.on('Attend', function(data) {
            var params = {
                eventID: event.id,
                status: data.status
            };

            if(data.guest) {
                params.guest = data.guest;
            }

            Application.callAPI({
                method: 'Events::attend',
                params: params,
                onSuccess: this.refresh.bind(this)
            });
        }.bind(this));

        view.on('Unattend', function(data) {
            var params = {
                attendeeID: data.attendeeID
            };

            Application.callAPI({
                method: 'Events::unattend',
                params: params,
                onSuccess: this.refresh.bind(this)
            });
        }.bind(this));

        view.on('Cancel', function() {
            Application.callAPI({
                method: 'Events::cancel',
                params: event.id,
                onSuccess: this.refresh.bind(this)
            });
        }.bind(this));

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
    },
    refresh: function(event) {
        event.myAttendees = event.myAttendees || [];
        event.statuses = event.statuses.split('|');
        event.owned = event.category.mySubscriptions[0].owner === 1;

        var attendees = {};

        event.myAttendees.forEach(function(attendee) {
            if(!attendees[attendee.status]) {
                attendees[attendee.status] = [];
            }

            if(attendee.createdBy.id == Application.session.id) {
                attendee.deletable = true;
            }

            attendees[attendee.status].push(attendee);
        });

        event.myAttendees = attendees;
        this.view.build(event);
    }
});
