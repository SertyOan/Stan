import View from 'oyat/UI/View.js';
import './style.css';

export default View.extend({
    __construct: function() {
        this.__parent();
        this.addType('main-view');
    },
    setScreen: function(view) {
        this.clear();
        this.add(view);
    }
});
