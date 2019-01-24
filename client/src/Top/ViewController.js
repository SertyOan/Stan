import Class from 'oyat/Class';
import Application from '../Application';
import User from '../Model/User';

export default Class.extend({
    __construct: function(view) {
        Application.bus.on('ChangePage', function(options) {
            view.rebuild({
                nickname: Application.session.nickname,
                showAdminButton: (Application.session.role & User.ROLE_ADMINISTRATOR) !== 0,
                showHomeButton: options.key != 'Home'   
            });
        });

        view.on('GoToHome', function() {
            Application.bus.emit('ChangePage', { key: 'Home' });
        });

        view.on('GoToAdmin', function() {
            Application.bus.emit('ChangePage', { key: 'Admin' });
        });

        view.on('GoToProfile', function() {
            Application.bus.emit('ChangePage', { key: 'Profile' });
        });
    }
});
