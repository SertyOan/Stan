import fr from './i18n/fr';
import en from './i18n/en';

var Module = {
    languages: {
        fr: fr,
        en: en
    },
    translate: function() {
        var key = arguments[0];
        var replacements = Array.prototype.slice.call(arguments, 1);
        var language = (navigator.language || 'fr').substr(0, 2);

        if(Module.languages[language]) {
            language = 'fr';
        }

        var string = Module.languages[language][key] || key;

        if(replacements && replacements.length > 0) {
            string = string.replace(/{(\d+)}/g, function(match, number) { 
                return typeof replacements[number] != 'undefined' ? replacements[number] : match;
            });
        }

        return string;
    }
}

export default Module;
