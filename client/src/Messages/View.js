import View from 'oyat/UI/View';
import Textarea from 'oyat/UI/Textarea';
import Button from 'oyat/UI/Button';
import Helpers from 'oyat/Helpers';
import Label from 'oyat/UI/Label';
import ComboBox from 'oyat/UI/ComboBox';
import './style.css';

export default View.extend({
    __construct: function() {
        this.__parent();
        this.addType('messages-view');
    },
    showMessages: function(messages, withForm) {
        this.clear();

        this.add(new Label('Messages')).addType('special');

        var form = this.add(new View());
        form.addType('form');

        if(withForm) {
            var textarea = form.add(new Textarea());
            form.add(new Button({
                text: 'Envoyer'
            })).on('Click', function() {
                this.emit('CreateMessage', textarea.getValue());
            }.bind(this));
        }
        else {
            form.add(new Label('Sélectionnez un évènement pour pouvoir écrire un message'));
        }

        var delays = [{
            limit: 60000,
            text: 'il y a 1 minute'
        }, {
            limit: 2 * 60000,
            text: 'il y a 2 minutes'
        }, {
            limit: 5 * 60000,
            text: 'il y a 5 minutes'
        }, {
            limit: 10 * 60000,
            text: 'il y a 10 minutes'
        }, {
            limit: 15 * 60000,
            text: 'il y a 15 minutes'
        }, {
            limit: 30 * 60000,
            text: 'il y a 30 minutes'
        }]

        for (var i = 1; i < 24; i++) {
            delays.push({
                limit: i * 60 * 60000,
                text: 'il y a ' + i + ' heure' + (i === 1 ? '' : 's')
            });
        };

        for (var i = 1; i < 7; i++) {
            delays.push({
                limit: i * 24 * 60 * 60000,
                text: 'il y a ' + i + ' jour' + (i === 1 ? '' : 's')
            });
        }

        delays.push({
            limit: 7 * 24 * 60 * 60000,
            text: 'il y a plus d\'une semaine'
        });

        for (var i = 0, c = messages.length; i < c; i++) {
            var message = messages[i];

            var messageView = this.add(new View());
            messageView.addType('message');
            Helpers.Element.setAttributes(messageView.elements.root, { style: 'border-color:#' + message.category.color });

            var s = new Date().getTime() - message.createdAt * 1000;
            var at = 'à l\'instant';

            delays.forEach(function(delay) {
                if (s > delay.limit) {
                    at = delay.text;
                }
            });

            var html = '<div class="head"><div class="author">' + message.createdBy.nickname + '</div><div class="meta">' + at + ' dans ' + message.category.name + '</div></div>';
            html += '<div class="text">' + message.text.replace(/(\r\n|\n|\r)/gm, '<br/>') + '</div>';
            messageView.setHTML(html);
        }
    }
});
