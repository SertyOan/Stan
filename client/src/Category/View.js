import View from 'oyat/UI/View';
import HBox from 'oyat/UI/HBox';
import Helpers from 'oyat/Helpers';
import Label from 'oyat/UI/Label';
import Link from 'oyat/UI/Link';
import TextField from 'oyat/UI/TextField';
import ChoicesView from 'oyat/UI/ChoicesView';
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

        if(options.isOwner) {
            var block = this.add(new View());
            block.addType('block');
            Helpers.Element.setAttributes(block.elements.root, { style: 'border-color:#' + category.color });

            block.add(new Label('Évènements récurrents')).addType('bold');

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

            block.add(new Link({ text: 'Créer un évènement' }))
                .on('Click', this.showCreator.bind(this));

            this.creator = block.add(new View());
            this.creator.hide();
            this.creator.addType('creator');

            this.creatorContext = {};
        }

        block.add(new Label(i18n.translate('MEMBERS'))).addType('bold');

        category.mySubscriptions.forEach(function(subscription) {
            var subView = block.add(new View());
            subView.addType('member');
            subView.add(new Label(subscription.user.nickname)).addType('name');
            subView.add(new Label(subscription.role & 1 ? 'Administrateur' : 'Membre')).addType('role'); // TODO review
        });
    },
    showCreator: function() {
        this.creator.show();
        this.creator.clear();

        this.creator.add(new Label('Type d\'évènement'));

        var firstField = this.creator.add(new ChoicesView({
            choices: [{ value: 'RE', html: 'Récurrent' }, { value: 'PO', html: 'Ponctuel' }],
            maxChoices: 1,
            defaultValues: this.creatorContext.form ? [this.creatorContext.form] : []
        }));

        firstField.on('Change', function() {
            this.creatorContext = {};
            this.creatorContext.form = firstField.getValues()[0];
            this.showCreator();
        }.bind(this));

        this.creator.add(new TextField({ placeholder: 'heure de début', defaultValue: this.creatorContext.hour || '' }))
            .on('KeyUp', function(data) {
                this.creatorContext.hour = data.value;
            }.bind(this));
        this.creator.add(new TextField({ placeholder: 'durée (en minutes)', defaultValue: this.creatorContext.duration || '' }))
            .on('KeyUp', function(data) {
                this.creatorContext.duration = data.value;
            }.bind(this));

        if(this.creatorContext.form == 'PO') {
            this.creator.add(new Label('Jour de l\'évènement'));

            var line = this.creator.add(new HBox());

            line.add(new TextField({ placeholder: 'année', defaultValue: this.creatorContext.year || '' }))
                .on('KeyUp', function(data) {
                    this.creatorContext.year = data.value;
                }.bind(this));

            line.add(new TextField({ placeholder: 'mois', defaultValue: this.creatorContext.month || '' }))
                .on('KeyUp', function(data) {
                    this.creatorContext.month = data.value;
                }.bind(this));

            line.add(new TextField({ placeholder: 'jour', defaultValue: this.creatorContext.day || '' }))
                .on('KeyUp', function(data) {
                    this.creatorContext.day = data.value;
                }.bind(this));
        }
        else {
            var typeField = this.creator.add(new ChoicesView({
                choices: [
                    { value: 'DA', html: i18n.translate('EVERY_DAY') },
                    { value: 'WD', html: i18n.translate('EVERY_WEEKDAY') },
                    { value: 'WE', html: i18n.translate('EVERY_WEEK') },
                    { value: 'MO', html: i18n.translate('EVERY_MONTH') }
                ],
                maxChoices: 1,
                defaultValues: this.creatorContext.type ? [this.creatorContext.type] : []
            }));
            typeField.on('Change', function() {
                this.creatorContext.type = typeField.getValues()[0];
                this.showCreator();
            }.bind(this));

            switch(this.creatorContext.type) {
                case 'WE':
                    var weekDayField = this.creator.add(new ChoicesView({
                        choices: [1, 2, 3, 4, 5, 6, 0].map(function(id) {
                            return { value: id, html: i18n.translate('EVERY_DAY_' + id) };
                        }),
                        maxChoices: 1,
                        defaultValues: this.creatorContext.weekDay ? [this.creatorContext.weekDay] : []
                    }));
                    weekDayField.on('Change', function() {
                        this.creatorContext.weekDay = weekDayField.getValues()[0];
                    }.bind(this));
                    break;
                case 'MO':
                    this.creator.add(new TextField({ placeholder: 'jour du mois' }));
                    break;
            }
        }

        this.creator.add(new Button({ text: 'Créer' }))
            .on('Click', function() {
                this.emit('Create', this.creatorContext);
            }.bind(this));
    }
});
