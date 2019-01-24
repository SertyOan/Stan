import View from 'oyat/UI/View';
import Label from 'oyat/UI/Label';
import Link from 'oyat/UI/Link';
import './style.css';

export default View.extend({
    __construct: function() {
        this.__parent();
        this.addType('top-view');
    },
    rebuild: function(options) {
        this.clear();

        var view = this.add(new View());
        view.addType('title');
        view.setText('Plouzane Sport'); // TODO review

        if(!!options.nickname) {
            if(options.showHomeButton) {
                var view = this.add(new View());
                view.addType('clickable home');
                view.setText('Evènements');
                view.on('Click', this.emit.bind(this, 'GoToHome'));
            }

            if(options.showAdminButton) {
                var view = this.add(new View());
                view.addType('clickable admin');
                view.setText('Administration');
                view.on('Click', this.emit.bind(this, 'GoToAdmin'));
            }

            var view = this.add(new View());
            view.addType('clickable profile');
            view.setText(options.nickname);
            view.on('Click', this.emit.bind(this, 'GoToProfile'));
        }
    }
});
