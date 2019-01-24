var dependencies = [
    'require',
    'Oyat/UI/Window',
    'Oyat/UI/TextField',
    'Oyat/UI/Textarea',
    'Oyat/UI/Label',
    'Oyat/UI/Button',
    'Oyat/UI/HBox'
];

define('Events/EventsCreationWindow', dependencies, function(require) {
    var Window = require('Oyat/UI/Window'),
        HBox = require('Oyat/UI/HBox'),
        Button = require('Oyat/UI/Button'),
        TextField = require('Oyat/UI/TextField'),
        Textarea = require('Oyat/UI/Textarea');

    return Window.extend({
        __construct: function() {
            this.__parent({
                modal: true,
                title: 'Créer des évnènement',
                closable: true,
                width: '500px',
                left: '250px',
                top: '50px'
            });

            var textarea = this.add(new Textarea());
            this.add(new Button({ text: 'Créer' }))
                .on('Click', function() {
                    this.emit('CreateNewEvents', textarea.getValue().split("\n"));
                }.bind(this));
        }
    });
});
