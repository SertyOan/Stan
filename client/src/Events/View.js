import View from 'oyat/UI/View';
import Label from 'oyat/UI/Label';
import './style.css';

export default View.extend({
    __construct: function() {
        this.__parent();
        this.addType('events-view');
    },
    build: function() {
        this.clear();
        this.add(new Label('Prochains évènements')).addType('special');
    },
    showNoEvent: function() {
        var view = this.add(new View());
        view.addType('block');
        view.add(new Label('Aucun évènement prévu'));
    }
});
