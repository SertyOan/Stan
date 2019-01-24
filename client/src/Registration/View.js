import View from 'oyat/UI/View';
import Label from 'oyat/UI/Label';
import TextField from 'oyat/UI/TextField';
import Button from 'oyat/UI/Button';
import './style.css';

export default View.extend({
    __construct: function() {
        this.__parent();
        this.addType('registration-view');
        this.showForm();
    },
    showForm: function() {
        this.clear();

        var view = this.add(new View());
        view.addType('block');

        view.add(new Label('Pas identifié ? Pour créer un compte ou recevoir le mail d\'identification, entre ton email !'));
        var emailField = view.add(new TextField());
        view.add(new Button({
                text: 'Envoyer'
            }))
            .on('Click', function() {
                this.emit('Submit', {
                    email: emailField.getValue()
                });
            }.bind(this));
    },
    showConfirmation: function(text) {
        this.clear();

        var view = this.add(new View());
        view.addType('block');
        view.add(new Label('Email d\'identification envoyé, vérifiez votre dossier spam si vous ne recevez pas l\'email dans les 5 minutes'));
    }
});
