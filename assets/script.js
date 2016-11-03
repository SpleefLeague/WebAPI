$(document).ready(function() {
    
    var that = this;
    
    this.Steps = {
        staff: function(obj) {
            if (obj.ranks.length > 5) {
                obj.ranks = [
                    obj.ranks[0], 
                    obj.ranks[1], 
                    obj.ranks[2], 
                    obj.ranks[3],
                    obj.ranks[4],
                    { '...': '...' }
                ];
            }
            return obj;
        },
        leaderboard: function(obj) {
            if (obj.players.length > 5) {
                obj.players = [
                    obj.players[0],
                    obj.players[1],
                    obj.players[2],
                    obj.players[3],
                    obj.players[4],
                    { '...': '...' }
                ];
            }
            return obj;
        },
        leaderboard2: function(obj) {
            if (obj.players.length > 5) {
                var mid = Math.round(obj.players.length / 2) - 1;
                obj.players = [
                    { '...': '...' },
                    obj.players[mid-2],
                    obj.players[mid-1],
                    obj.players[mid],
                    obj.players[mid+1],
                    obj.players[mid+2],
                    { '...': '...' }
                ]
            }
            return obj;
        }
    };
    
    var Steps = that.Steps;
    
    this.get = function(url) {
        var s = '';
        $.get({
            url: url,
            async: false
        }, function(d) {
            s = d;
        }); 
        return s;
    }
    
    this.setContent = function(selector, url, step) {
        var obj = that.get(url);
        if (step != undefined) {
            obj = step(obj);
        }
        var content = JSON.stringify(obj, null, 4);
        content = content.replace(/\n/g, '<br />').replace(/    /g, '&nbsp;&nbsp;&nbsp;&nbsp;');
        $(selector).html('<code class="json">' + content + '</code>');
    }
    
    this.setContentRaw = function(selector, url) {
        var content = JSON.stringify(that.get(url), null, 4);
        if (content[0] == '"') {
            content = content.substr(1);   
        }
        if (content[content.length - 1] = '"') {
            content = content.substr(0, content.length - 1);
        }
        content = content
            .replace(/\\n/g, '<br />')
            .replace(/    /g, '&nbsp;&nbsp;&nbsp;&nbsp;')
            .replace(/\\"/g, '"');
        $(selector).html('<code class="json">' + content + '</code>');
    }
    
    that.setContentRaw('.code-block.stats-name', './assets/api-result/stats-name.txt');
    that.setContentRaw('.code-block.staff', './assets/api-result/staff.txt');
    that.setContentRaw('.code-block.ranks', './assets/api-result/ranks.txt');
    that.setContentRaw('.code-block.leaderboard-superjump', './assets/api-result/superjump.txt');
    that.setContentRaw('.code-block.leaderboard-spleef', './assets/api-result/spleef.txt');
    
    /*that.setContent('.code-block.stats-name', 'http://api.spleefleague.com/stats/name/Wouto1997');
    that.setContent('.code-block.staff', 'http://api.spleefleague.com/staff', Steps.staff);
    that.setContent('.code-block.ranks', 'http://api.spleefleague.com/ranks', Steps.staff);
    that.setContent('.code-block.leaderboard-superjump', 'http://api.spleefleague.com/leaderboard/superjump/3', Steps.leaderboard);
    that.setContent('.code-block.leaderboard-spleef', 'http://api.spleefleague.com/leaderboard/spleef/player/Wouto1997', Steps.leaderboard2);*/
    
    $("p.code-block").each(function(i, block) {
        hljs.highlightBlock(block); 
    });
    
    window["scriptContext"] = this;
    
});