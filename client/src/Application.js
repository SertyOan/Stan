import XHR from 'oyat/XHR.js';
import Helpers from 'oyat/Helpers.js';
import EventBus from 'oyat/EventBus';
import Viewport from 'oyat/UI/Viewport.js';
import Notifier from 'oyat/UI/Notifier.js';
import TopView from './Top/View';
import TopViewController from './Top/ViewController';
import MainView from './Main/View.js';
import MainViewController from './Main/ViewController.js';
import './Application.css';

var Application = {
    load: function() {
        Application.loader = {
            mask: document.body.appendChild(Helpers.Element.create('div', {
                className: 'loader-mask'
            })),
            progress: document.body.appendChild(Helpers.Element.create('div', {
                className: 'loader-progress'
            })),
            interval: false,
            completion: 0,
            pending: 0,
            start: function() {
                if(Application.loader.pending === 0) {
                    Helpers.Element.setAttributes(Application.loader.progress, { style: 'transition:none;width:0%;height:5px' });
                    Helpers.Element.setAttributes(Application.loader.mask, { style: 'display:block' });

                    Application.loader.interval = window.setInterval(function() {
                        if(Application.loader.completion > 80) {
                            Application.loader.completion += 0.1;
                        }
                        else if(Application.loader.completion > 50) {
                            Application.loader.completion += 1;
                        }
                        else {
                            Application.loader.completion += 10;
                        }

                        Application.loader.completion = Math.min(95, Application.loader.completion);
                        Helpers.Element.setAttributes(Application.loader.progress, { style: 'transition:width 0.25s ease-in,height 0.5s linear 0.5s;width:' + Application.loader.completion + '%;height:5px' });
                    }, 25);
                }

                Application.loader.pending++;
            },
            end: function() {
                Application.loader.pending--;

                if (Application.loader.pending === 0) {
                    window.clearInterval(Application.loader.interval);
                    Application.loader.interval = false;
                    Application.loader.completion = 0;

                    Helpers.Element.setAttributes(Application.loader.progress, { style: 'transition:width 0.25s ease-in,height 0.5s linear 0.5s;width:100%;height:0' });
                    Helpers.Element.setAttributes(Application.loader.mask, { style: '' });
                }
            }
        };

        Application.bus = new EventBus();
        Application.viewport = new Viewport('viewport');
        Application.notifier = Application.viewport.add(new Notifier());
        Application.notifier.addType('fixed');

        var topView = new TopView();
        new TopViewController(topView);
        Application.viewport.add(topView);

        var mainView = new MainView();
        new MainViewController(mainView);
        Application.viewport.add(mainView);
    },
    refresh: function(callback) {
        Application.callAPI({
            method: 'Application::session',
            onSuccess: function(session) {
                Application.session = session;
                callback();
            }
        });
    },
    callAPI: function(options) {
        options = options || {};
        options.onException = options.onException || function(e) {
            Application.notifier.notify(e.message);
            console.log(e);
        };

        options.onSuccess = options.onSuccess || function() { };

        var myUUID = Math.random().toString(36).slice(2);

        Application.loader.start();

        XHR.callBasic('/service/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Stan-Token': location.hash.replace('#', '')
            },
            postBody: JSON.stringify({
                id: myUUID,
                method: options.method,
                params: options.params || null
            }),
            on200: function(transport) {
                Application.loader.end();

                try {
                    var object = JSON.parse(transport.responseText);

                    if (!object || !('id' in object) || (!('result' in object) && !('error' in object))) {
                        throw new Error('Response is misformed');
                    }

                    if (object.id != myUUID) {
                        throw new Error('Request id and response id do not match');
                    }

                    if (!!object.error) {
                        // TODO check error structure (should have a code)
                        options.onException({ message: object.error.message, code: object.error.code });
                    }
                    else {
                        options.onSuccess(object.result);
                    }
                } catch (e) {
                    options.onException({ message: e.message, code: 0 });
                }
            },
            onSuccess: function(transport) {
                Application.loader.end();
                options.onException(new Error('Unexpected HTTP code'));
            },
            onFailure: function(transport) {
                Application.loader.end();
                options.onException(new Error('Unexpected error'));
            }
        });
    }
};

export default Application;
