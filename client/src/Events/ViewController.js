import Class from 'oyat/Class';
import Application from '../Application';
import EventView from '../Event/View';
import EventViewController from '../Event/ViewController';

export default Class.extend({
    __construct: function(view) {
        view.on('Render', function() {
            Application.callAPI({
                method: 'Events::search',
                onSuccess: function(events) {
                    view.build();

                    if(events.length === 0) {
                        view.showNoEvent();
                    }
                    else {
                        events.forEach(function(event) {
                            var sub = new EventView();
                            new EventViewController(sub, event);
                            view.add(sub);
                        });
                    }
                }
            });
        });
    }
});
