import View from 'oyat/UI/View';
import HBox from 'oyat/UI/HBox';
import Helpers from 'oyat/Helpers';
import Label from 'oyat/UI/Label';
import Button from 'oyat/UI/Button';
import './style.css';

export default View.extend({
    __construct: function() {
        this.__parent();
        this.addType('category-view');
    },
    build: function(category, options) {
        this.clear();
        this.add(new Label(category.name)).addType('special');

        if(options.isAdministrator) {
            var block = this.add(new View());
            block.addType('block');
            Helpers.Element.setAttributes(block.elements.root, { style: 'border-color:#' + category.color });

            block.add(new HBox())
                .add(new Button({ text: 'Supprimer' }))
                    .on('Click', function() {
                        // this.emit.bind(this, 'Delete')
                    });
        }

        var block = this.add(new View());
        block.addType('block');
        Helpers.Element.setAttributes(block.elements.root, { style: 'border-color:#' + category.color });

        if(options.isMember) {
            block.add(new HBox())
                .add(new Button({ text: 'Se désinscrire' }))
                    .on('Click', this.emit.bind(this, 'Unsubscribe'));
        }
        else {
            block.add(new HBox())
                .add(new Button({ text: 'S\'inscrire' }))
                    .on('Click', this.emit.bind(this, 'Subscribe'));
        }

        block.add(new Label('Membres')).addType('bold');

        category.mySubscriptions.forEach(function(subscription) {
            block.add(new Label(subscription.user.nickname));
        });

        if(options.isOwner) {
            var block = this.add(new View());
            block.addType('block');
            Helpers.Element.setAttributes(block.elements.root, { style: 'border-color:#' + category.color });

            block.add(new Label('Evènements récurrents')).addType('bold');

            category.myRecurrences.forEach(function(recurrence) {
                block.add(new Label(JSON.stringify(recurrence)));
            });
        }
    }
});
