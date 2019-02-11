import View from 'oyat/UI/View';
import Label from 'oyat/UI/Label';
import Link from 'oyat/UI/Link';
import Helpers from 'oyat/Helpers';
import TextField from 'oyat/UI/TextField';
import './style.css';

export default View.extend({
    __construct: function() {
        this.__parent();
        this.addType('event-view');
        this.focused = false;
    },
    build: function(event) {
        this.clear();
        this.overlay = this.add(new View());
        this.overlay.addType('overlay');
        this.overlay.hide();

        var statuses = event.statuses.split('|');

        var attendees = {};

        event.myAttendees = event.myAttendees || []; // TODO do it in controller

        event.myAttendees.forEach(function(attendee) {
            if(!attendees[attendee.status]) {
                attendees[attendee.status] = [];
            }

            attendees[attendee.status].push(attendee);
        });

        var head = this.add(new View());
        head.addType('head');
        Helpers.Element.setAttributes(head.elements.root, { style: 'border-color:#' + event.category.color });

        head.on('Click', this.emit.bind(this, 'HeadClick'));

        head.add(new Label(event.category.name));

        var startDate = new Date(event.startAt * 1000);
        var days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        var monthes = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        var endDate = new Date(event.endAt * 1000);

        var title = days[startDate.getDay()] + ' ' + startDate.getDate() + ' ' + monthes[startDate.getMonth()] + ' ';
        title += ('0' + startDate.getHours()).substr(-2) + 'H';
        title += ('0' + startDate.getMinutes()).substr(-2);
        title += ' - ';
        title += ('0' + endDate.getHours()).substr(-2) + 'H';
        title += ('0' + endDate.getMinutes()).substr(-2);

        head.add(new Label(title)).addType('date');

        var texts = [];
        
        statuses.forEach(function(status) {
            attendees[status] = attendees[status] || [];
            texts.push(status + ': ' + attendees[status].length);
        }.bind(this));

        this.summary = head.add(new Label(texts.join(', ')));

        this.form = this.add(new View());
        this.form.addType('form');

        if(this.focused === false) {
            this.form.hide();
        }

        statuses.forEach(function(status) {
            attendees[status] = attendees[status] || [];

            var subView = this.form.add(new View());
            subView.addType('status');
            subView.add(new Label(status + ' (' + attendees[status].length + ')')).addType('title');

            attendees[status].forEach(function(attendee) {
                subView.add(new Label(attendee.createdBy.nickname));
            });
        }.bind(this));

        var actions = this.form.add(new View());
        actions.addType('actions');

        statuses.forEach(function(status) {
            actions.add(new Link({
                    text: status
                }))
                .on('Click', this.emit.bind(this, 'Attend', status));
        }.bind(this));

        var guestAdd = actions.add(new Link({ text: 'Ajout d\'invité' }));
        guestAdd.addType('guest');
        guestAdd.on('Click', this.showGuestOverlay.bind(this, event));
    },
    showGuestOverlay: function(event) {
        this.overlay.show();
        this.overlay.clear();

        var nameField = this.overlay.add(new TextField({ placeholder: 'Nom de l\'invité' }));

        var statuses = event.statuses.split('|');

        statuses.forEach(function(status) {
            this.overlay.add(new Link({
                    text: status
                }))
                .on('Click', this.emit.bind(this, 'AddGuest', {
                    name: nameField.getValue(),
                    status: status
                }));
        }.bind(this));
    },
    focus: function() {
        this.focused = true;
        this.form.show();
        this.summary.hide();
    },
    unfocus: function() {
        this.focused = false;
        this.form.hide();
        this.summary.show();
    }
});
