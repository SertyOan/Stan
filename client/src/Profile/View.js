import View from 'oyat/UI/View';
import Label from 'oyat/UI/Label';
import Button from 'oyat/UI/Button';
import TextField from 'oyat/UI/TextField';
import './style.css';

export default View.extend({
    __construct: function() {
        this.__parent();
        this.addType('profile-view');

        this.add(new Label('Profil')).addType('special');

        var block = this.add(new View());
        block.addType('block');

        var field = block.add(new TextField({ placeholder: 'nouveau nom' }));
        block.add(new Button({ text: 'Changer' }))
            .on('Click', function() {
                this.emit('ChangeName', field.getValue());
            }.bind(this));

        var block = this.add(new View());
        block.addType('block');
        block.add(new Label('Actions'));
        block.add(new Button({ text: 'Supprimer mon compte' }))
            .on('Click', this.emit.bind(this, 'Confirm', { action: 'DeleteUser' }));

        block.add(new Button({ text: 'Reinitialiser ma clé' }))
            .on('Click', this.emit.bind(this, 'Confirm', { action: 'ReinitializeKey' }));
    }
});
