angular.module('mm.addons.qtype_multichoiceset', ['mm.core'])
.config(["$mmQuestionDelegateProvider", function($mmQuestionDelegateProvider) {
    $mmQuestionDelegateProvider.registerHandler('mmaQtypeMultichoiceSet', 'qtype_multichoiceset', '$mmaQtypeMultichoiceSetHandler');
}]);

angular.module('mm.addons.qtype_multichoice')
.directive('mmaQtypeMultichoice', ["$log", "$mmQuestionHelper", function($log, $mmQuestionHelper) {
	$log = $log.getInstance('mmaQtypeMultichoiceSet');
    return {
        restrict: 'A',
        priority: 100,
        templateUrl: 'addons/qtype/multichoiceset/template.html',
        link: function(scope) {
        	$mmQuestionHelper.multiChoiceDirective(scope, $log);
        }
    };
}]);

angular.module('mm.addons.qtype_multichoiceset')
.factory('$mmaQtypeMultichoiceSetHandler', ["$mmUtil", function($mmUtil) {
    var self = {};
        self.isCompleteResponse = function(question, answers) {
        var isSingle = true,
            isMultiComplete = false;
        angular.forEach(answers, function(value, name) {
            if (name.indexOf('choice') != -1) {
                isSingle = false;
                if (value) {
                    isMultiComplete = true;
                }
            }
        });
        if (isSingle) {
            return self.isCompleteResponseSingle(answers);
        } else {
            return isMultiComplete;
        }
    };
        self.isCompleteResponseSingle = function(answers) {
        return answers['answer'] && answers['answer'] !== '';
    };
        self.isEnabled = function() {
        return true;
    };
        self.isGradableResponse = function(question, answers) {
        return self.isCompleteResponse(question, answers);
    };
        self.isGradableResponseSingle = function(answers) {
        return self.isCompleteResponseSingle(answers);
    };
        self.isSameResponse = function(question, prevAnswers, newAnswers) {
        var isSingle = true,
            isMultiSame = true;
        angular.forEach(newAnswers, function(value, name) {
            if (name.indexOf('choice') != -1) {
                isSingle = false;
                if (!$mmUtil.sameAtKeyMissingIsBlank(prevAnswers, newAnswers, name)) {
                    isMultiSame = false;
                }
            }
        });
        if (isSingle) {
            return self.isSameResponseSingle(prevAnswers, newAnswers);
        } else {
            return isMultiSame;
        }
    };
        self.isSameResponseSingle = function(prevAnswers, newAnswers) {
        return $mmUtil.sameAtKeyMissingIsBlank(prevAnswers, newAnswers, 'answer');
    };
        self.getDirectiveName = function(question) {
        return 'mma-qtype-multichoice-set';
    };
    return self;
}]);
