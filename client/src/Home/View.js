import View from 'oyat/UI/View';
import Label from 'oyat/UI/Label';
import Helpers from 'oyat/Helpers';
import './style.css';

export default View.extend({
    __construct: function() {
        this.__parent();
        this.addType('home-view');
    }
});
