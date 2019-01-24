import View from 'oyat/UI/View';
import Label from 'oyat/UI/Label';
import Helpers from 'oyat/Helpers';
import './style.css';

export default View.extend({
    __construct: function() {
        this.__parent();
        this.addType('categories-view');
    },
    build: function(title, categories) {
        this.clear();
        this.add(new Label(title)).addType('special');

        categories.forEach(this.addCategory.bind(this));
    },
    addCategory: function(category) {
        var view = this.add(new View());
        view.addType('category');
        Helpers.Element.setAttributes(view.elements.root, { style: 'border-color:#' + category.color });
        view.setText(category.name);
        view.on('Click', this.emit.bind(this, 'GoToCategory', category.id));
    }
});
