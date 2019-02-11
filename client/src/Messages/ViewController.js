import Class from 'oyat/Class';
import Application from '../Application';

export default Class.extend({
    __construct: function(view, context) {
        this.view = view;
        this.categoryID = -1;

        view.on('Render', this.refresh.bind(this));

        view.on('CreateMessage', function(text) {
            Application.callAPI({
                method: 'Messages::create',
                params: {
                    categoryID: this.categoryID,
                    text: text
                },
                onSuccess: function(success) {
                    Application.notifier.notify('Message saved', {
                        timeout: 2000
                    });
                    this.refresh();
                }.bind(this)
            });
        }.bind(this));

        Application.bus.on('FocusEvent', function(event) {
            this.categoryID = event.category.id;
            this.refresh();
        }.bind(this));

        Application.bus.on('UnfocusEvent', function() {
            this.categoryID = -1;
            this.refresh();
        }.bind(this));
    },
    refresh: function() {
        Application.callAPI({
            method: 'Messages::search',
            params: {
                categoryID: this.categoryID
            },
            onSuccess: function(messages) {
                this.view.showMessages(messages, this.categoryID != -1);
            }.bind(this)
        });
    }
});
