app.service('apiService', function() {
    this.domain = {
        develop: ''
    };
    this.api = {
       
    };
    this.getUrl = function(apiName) {
        return this.domain['develop'] + this.api[apiName]
    }
})
