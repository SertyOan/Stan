import View from 'oyat/UI/View';
import Label from 'oyat/UI/Label';
import TextField from 'oyat/UI/TextField';
import Textarea from 'oyat/UI/Textarea';
import Button from 'oyat/UI/Button';
import './style.css';

export default View.extend({
    __construct: function() {
        this.__parent();
        this.addType('admin-view');
    },
    build: function() {
        this.clear();

        this.add(new Label('Administration')).addType('special');

        var block = this.add(new View());
        block.addType('block');

        block.add(new Label('Créer un nouveau groupe'));
        var nameField = block.add(new TextField({ placeholder: 'nom du groupe' }));
        var colorField = block.add(new TextField({ placeholder: 'couleur du groupe' }));
        block.add(new Button({ text: 'Créer' }))
            .on('Click', function() {
                this.emit('CreateCategory', {
                    name: nameField.getValue(),
                    color: colorField.getValue()
                });
            }.bind(this));


        var block = this.add(new View());
        block.addType('block');

        block.add(new Label('Envoyer un mail'));
        var titleField = block.add(new TextField({ placeholder: 'titre du mail' }));
        var contentField = block.add(new Textarea({ placeholder: 'contenu du mail' }));
        block.add(new Button({ text: 'Envoyer' }))
            .on('Click', function() {
                this.emit('SendMail', {
                    title: titleField.getValue(),
                    content: contentField.getValue()
                });
            }.bind(this));

    }
});
