import Class from 'oyat/Class';
import Application from '../Application';
import * as Screens from '../Screens';

export default Class.extend({
    __construct: function(view) {
        this.view = view;

        window.addEventListener('hashchange', this.onHashChange.bind(this), false);

        view.on('Render', this.onHashChange.bind(this));

        Application.bus.on('ChangePage', function(data) {
            var key = data.key;
            var options = data.options;

            var subView = new Screens[key + 'View'];
            new Screens[key + 'ViewController'](subView, options);
            view.setScreen(subView);
        });
    },
    onHashChange: function() {
        this.view.clear();

        Application.refresh(function() {
            if(Application.session.id) {
                Application.bus.emit('ChangePage', { key: 'Home' });
            }
            else {
                Application.bus.emit('ChangePage', { key: 'Registration' });
            }
        });
    }
});
