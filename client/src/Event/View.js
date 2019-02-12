import View from 'oyat/UI/View';
import Label from 'oyat/UI/Label';
import Link from 'oyat/UI/Link';
import HBox from 'oyat/UI/HBox';
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

        this.overlay.elements.root.addEventListener('click', function(browserEvent) {
            if(browserEvent.target === this.overlay.elements.root) {
                this.overlay.hide();
            }
        }.bind(this));


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
        
        event.statuses.forEach(function(status) {
            event.myAttendees[status] = event.myAttendees[status] || [];
            texts.push(status + ': ' + event.myAttendees[status].length);
        }.bind(this));

        this.summary = head.add(new Label(texts.join(', ')));

        this.form = this.add(new View());
        this.form.addType('form');

        if(this.focused === false) {
            this.form.hide();
        }

        event.statuses.forEach(function(status) {
            var subView = this.form.add(new View());
            subView.addType('status');
            subView.add(new Label(status + ' (' + event.myAttendees[status].length + ')')).addType('title');

            event.myAttendees[status].forEach(function(attendee) {
                var line = subView.add(new HBox());
                
                if(attendee.deletable) {
                    line.add(new Link({ text: 'X' }), { width: '20px' })
                        .on('Click', this.emit.bind(this, 'Unattend', { attendeeID: attendee.id }));
                }
                else {
                    line.add(new View(), { width: '20px' }).setHTML('&nbsp;');
                }

                if(attendee.guest) {
                    var label = line.add(new Label(attendee.guest + ' #invité'));
                    label.addType('guest');
                }
                else {
                    line.add(new Label(attendee.createdBy.nickname));
                }
            }.bind(this));
        }.bind(this));

        var actions = this.form.add(new View());
        actions.addType('actions');

        event.statuses.forEach(function(status) {
            actions.add(new Link({
                    text: status
                }))
                .on('Click', this.emit.bind(this, 'Attend', { status: status }));
        }.bind(this));

        var guestAdd = actions.add(new Link({ text: 'Ajout d\'invité' }));
        guestAdd.addType('guest');
        guestAdd.on('Click', this.showGuestOverlay.bind(this, event));
    },
    showGuestOverlay: function(event) {
        this.overlay.show();
        this.overlay.clear();

        var form = this.overlay.add(new View())
        form.addType('guest-form');

        form.add(new Label('Nom de l\'invité'));
        var nameField = form.add(new TextField());

        form.add(new Label('Statut de l\'invité'));
        event.statuses.forEach(function(status) {
            var link = form.add(new Link({ text: status }));
            link.on('Click', function(selected) {
                this.emit('Attend', {
                    guest: nameField.getValue(),
                    status: selected
                });
            }.bind(this, status));
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
        this.overlay.hide();
        this.summary.show();
    }
});
