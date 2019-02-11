import View from 'oyat/UI/View';
import HBox from 'oyat/UI/HBox';
import Helpers from 'oyat/Helpers';
import Label from 'oyat/UI/Label';
import Button from 'oyat/UI/Button';
import i18n from '../i18n';
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
                .add(new Button({ text: i18n.translate('DELETE') }))
                    .on('Click', function() {
                        // this.emit.bind(this, 'Delete')
                    });
        }

        var block = this.add(new View());
        block.addType('block');
        Helpers.Element.setAttributes(block.elements.root, { style: 'border-color:#' + category.color });

        if(options.isMember) {
            block.add(new HBox())
                .add(new Button({ text: i18n.translate('UNSUBSCRIBE') }))
                    .on('Click', this.emit.bind(this, 'Unsubscribe'));
        }
        else {
            block.add(new HBox())
                .add(new Button({ text: i18n.translate('SUBSCRIBE') }))
                    .on('Click', this.emit.bind(this, 'Subscribe'));
        }

        block.add(new Label(i18n.translate('MEMBERS'))).addType('bold');

        category.mySubscriptions.forEach(function(subscription) {
            block.add(new Label(subscription.user.nickname));
        });

        if(options.isOwner) {
            var block = this.add(new View());
            block.addType('block');
            Helpers.Element.setAttributes(block.elements.root, { style: 'border-color:#' + category.color });

            block.add(new Label('Evènements récurrents')).addType('bold');

            category.myRecurrences.forEach(function(recurrence) {
                switch(recurrence.type) {
                    case 'DA': var text = i18n.translate('EVERY_DAY'); break;
                    case 'WD': var text = i18n.translate('EVERY_WEEKDAY'); break;
                    case 'WE': var text = i18n.translate('EVERY_DAY_' + recurrence.weekDay); break;
                    case 'WD': var text = i18n.translate('EVERY_MONTH_ON', recurrence.monthDay); break;
                }

                var description = i18n.translate('EVENT_DESCRIPTION', text, ('0' + recurrence.hour).substr(-2), ('0' + recurrence.minute).substr(-2), recurrence.timezone, recurrence.duration);

                block.add(new Label(description));
            });
        }
    }
});
