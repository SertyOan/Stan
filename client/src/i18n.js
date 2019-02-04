import fr from 'i18n/fr';
import en from 'i18n/en';

var Module = {
    languages: {
        fr: fr,
        en: en
    },
    translate: function() {
        var key = arguments[0];
        var replacements = Array.prototype.slice.call(arguments, 1);
        var language = 'fr';

        if(navigator.language && Module.languages[navigator.language]) {
            language = navigator.language;
        }

        var string = Module.languages[language] || key;

        if(replacements && replacements.length > 0) {
            string.replace(/{(\d+)}/g, function(match, number) { 
                return typeof replacements[number] != 'undefined' ? replacements[number] : match;
            });
        }

        return string;
    }
}

export default Module;
