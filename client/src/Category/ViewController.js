import Class from 'oyat/Class';
import Application from '../Application';
import Subscription from '../Model/Subscription';
import User from '../Model/User';

export default Class.extend({
    __construct: function(view, options) {
        this.view = view;
        this.options = options;

        view.on('Render', this.refresh.bind(this));

        view.on('Subscribe', function() {
            Application.callAPI({
                method: 'Subscriptions::create',
                params: {
                    categoryID: options.categoryID
                },
                onSuccess: function(success) {
                    Application.notifier.notify('Inscription enregistrée');
                    this.refresh();
                }.bind(this)
            });
        }.bind(this));

        view.on('Unsubscribe', function() {
            Application.callAPI({
                method: 'Subscriptions::delete',
                params: {
                    categoryID: options.categoryID
                },
                onSuccess: function(success) {
                    Application.notifier.notify('Inscription supprimée');
                    this.refresh();
                }.bind(this)
            });
        }.bind(this));

        view.on('Promote', function(subscriptionID) {
             Application.callAPI({
                method: 'Subscriptions::promote',
                params: {
                    subscriptionID: subscriptionID
                },
                onSuccess: function(success) {
                    Application.notifier.notify('Promotion effectuée');
                    this.refresh();
                }.bind(this)
            });
        }.bind(this));

        view.on('Demote', function(subscriptionID) {
             Application.callAPI({
                method: 'Subscriptions::demote',
                params: {
                    subscriptionID: subscriptionID
                },
                onSuccess: function(success) {
                    Application.notifier.notify('Rétrogradation effectuée');
                    this.refresh();
                }.bind(this)
            });
        }.bind(this));

        view.on('CreateEvent', function(data) {
            data.categoryID = options.categoryID;

            switch(data.form) {
                case 'PO':
                    var method = 'Events::create';
                    break;
                case 'RE':
                    var method = 'Recurrences::create';
                    break;
                default:
                    Application.notifier.notify('Type invalide');
            }

            if(method) {
                Application.callAPI({
                    method: method,
                    params: data,
                    onSuccess: function(success) {
                        Application.notifier.notify('Evènement créé');
                        this.refresh();
                    }.bind(this)
                });
            }
        }.bind(this));

        view.on('DeleteRecurrence', function(data) {
            Application.callAPI({
                method: 'Recurrences::delete',
                params: data,
                onSuccess: function(success) {
                    Application.notifier.notify('Evènement récurrent supprimé');
                    this.refresh();
                }.bind(this)
            });
        }.bind(this));

        view.on('Delete', function() {
            Application.callAPI({
                method: 'Categories::delete',
                params: {
                    categoryID: options.categoryID
                },
                onSuccess: function(success) {
                    Application.bus.emit('ChangePage', { key: 'Home' });
                    Application.notifier.notify('Catégorie supprimée');
                }.bind(this)
            });
        }.bind(this));
    },
    refresh: function() {
        Application.callAPI({
            method: 'Categories::get',
            params: {
                categoryID: this.options.categoryID
            },
            onSuccess: function(category) {
                category.mySubscriptions = category.mySubscriptions || [];
                category.myRecurrences = category.myRecurrences || [];

                var options = {
                    isMember: false,
                    isOwner: (Application.session.role & User.ROLE_ADMINISTRATOR) != 0,
                    isAdministrator: (Application.session.role & User.ROLE_ADMINISTRATOR) != 0
                };

                category.mySubscriptions.forEach(function(subscription) {
                    if(subscription.user.id === Application.session.id) {
                        options.isMember = true;

                        if(subscription.owner) {
                            options.isOwner = true;
                        }
                    }
                });

                this.view.build(category, options);
            }.bind(this)
        });
    }
});
