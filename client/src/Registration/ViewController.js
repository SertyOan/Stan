import Class from 'oyat/Class';
import Application from '../Application';

export default Class.extend({
    __construct: function(view) {
        view.on('Submit', function(data) {
            Application.callAPI({
                method: 'Users::create',
                params: {
                    email: data.email
                },
                onSuccess: function(success) {
                    if (success) {
                        view.showConfirmation();
                    } else {
                        Application.notifier.notify('Email invalide');
                    }
                }
            });
        });
    }
});


