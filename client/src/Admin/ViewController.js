import Class from 'oyat/Class';
import Application from '../Application';

export default Class.extend({
    __construct: function(view) {
        view.on('Render', function() {
            view.build();
        });

        view.on('CreateCategory', function(params) {
            Application.callAPI({
                method: 'Categories::create',
                params: params,
                onSuccess: function() {
                    Application.notifier.notify('Groupe créé', { timeout: 2500 });
                    view.build();
                }
            });
        });
    }
});
