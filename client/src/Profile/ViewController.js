import Class from 'oyat/Class';
import Application from '../Application';

export default Class.extend({
    __construct: function(view) {
        view.on('ChangeName', function(name) {
            Application.callAPI({
                method: 'Users::rename',
                params: {
                    name: name
                },
                onSuccess: function() {
                    Application.refresh(function() {
                        Application.notifier.notify('Modification du nom effectuée');
                        Application.bus.emit('ChangePage', { key: 'Profile' });
                    });
                }
            });
        });

        view.on('Confirm', function(options) {

        });

        view.on('DeleteUser', function() {

        });

        view.on('ReinitializeKey', function() {

        });
    }
});
