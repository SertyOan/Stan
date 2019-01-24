import Class from 'oyat/Class';
import Application from '../Application';

export default Class.extend({
    __construct: function(view, options) {
        view.on('Render', function() {
            Application.callAPI({
                method: 'Categories::search',
                params: {
                    subscribed: options.subscribed
                },
                onSuccess: function(categories) {
                    view.build(options.title, categories);
                }
            });
        });

        view.on('GoToCategory', function(categoryID) {
            Application.bus.emit('ChangePage', {
                key: 'Category',
                options: { categoryID: categoryID }
            });
        });
    }
});
