import Class from 'oyat/Class';
import Application from '../Application';
import CategoriesView from '../Categories/View';
import CategoriesViewController from '../Categories/ViewController';
import EventsView from '../Events/View';
import EventsViewController from '../Events/ViewController';
import MessagesView from '../Messages/View';
import MessagesViewController from '../Messages/ViewController';

export default Class.extend({
    __construct: function(view) {
        var eventsView = new EventsView();
        new EventsViewController(eventsView);
        view.add(eventsView);

        var messagesView = new MessagesView();
        new MessagesViewController(messagesView);
        view.add(messagesView);

        var categoriesView = new CategoriesView();
        new CategoriesViewController(categoriesView, {
            title: 'Mes groupes',
            subscribed: true
        });
        view.add(categoriesView);

        var allCategoriesView = new CategoriesView();
        new CategoriesViewController(allCategoriesView, {
            title: 'Autres groupes',
            subscribed: false
        });
        view.add(allCategoriesView);
    }
});
