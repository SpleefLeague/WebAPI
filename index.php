<html>
    <head>
        <script type="text/javascript" src="./assets/jquery-3.1.1.min.js"></script>
        <script type="text/javascript" src="./assets/script.js"></script>
        <link rel="stylesheet" href="./assets/style.css">
        <link rel="stylesheet" href="./assets/styles/default.css">
        <script type="text/javascript" src="./assets/highlight.pack.js"></script>
        <script type="text/javascript">hljs.initHighlightingOnLoad();</script>
        <title>SpleefLeague API</title>
    </head>
    <body>

        <div id="header">
            <h1>SpleefLeague API</h1>
        </div>

        <div class="content">
            <h3>Documentation</h3>
            Welcome to the SpleefLeague API Documentation. The available API functions are:
            <ul>
                <li><a href="#player-stats">Player stats</a></li>
                <li><a href="#staff">Staff list</a></li>
                <li><a href="#ranks">Rank list</a></li>
                <li><a href="#leaderboards">Leaderboards</a></li>
            </ul>
        </div>

        <div class="content">
            <h3><a class="content-header" name="player-stats">Player stats</a></h3>
            The player's stats can be fetched using the following urls. For searching by username:
            <p class="url-line">http://api.spleefleague.com/stats/name/{username}</p>
            And for searching by uuid:
            <p class="url-line">http://api.spleefleague.com/stats/uuid/{uuid}</p>
            For example, a request to <span class="inline-code">http://api.spleefleague.com/stats/name/Tobyyy</span> will give the following output:
            <p class="code-block stats-name">

            </p>
        </div>

        <div class="content">
            <h3><a class="content-header" name="staff">Staff list</a></h3>
            The staff list can be fetched using the following url:
            <p class="url-line">http://api.spleefleague.com/staff</p>
            Which results in an output that looks like this:
            <p class="code-block staff">

            </p>
        </div>

        <div class="content">
            <h3><a class="content-header" name="ranks">Rank list</a></h3>
            The rank list can be fetched using the following url:
            <p class="url-line">http://api.spleefleague.com/ranks</p>
            Which results in an output that looks like this:
            <p class="code-block ranks">

            </p>
        </div>

        <div class="content">
            <h3><a class="content-header" name="leaderboards">Leaderboards</a></h3>
            The leaderboards can be fetched using the following url schemes:
            <p class="url-line">http://api.spleefleague.com/leaderboard/{game}[/page]</p>
            <p class="url-line">http://api.spleefleague.com/leaderboard/{game}/player/{username/id}</p>
            Valid game names are:
            <p class="code-block">Snowspleef: spleef|snowspleef|ss|superspleef</p>
            <p class="code-block">Superjump: jump|superjump|sj</p>
            The <span class="inline-code">/leaderboard/{game}/</span> page shows the first 100 results on the leaderboard.
            In order to get the next pages use the <span class="inline-code">/leaderboard/{game}/{page}</span> endpoint, 
            where page 2 will show the players from 101-200. For example, to get the players from position 201-300 in superjump, use: 
            <span class="inline-code">/leaderboard/superjump/3</span> which gives the following output:
            <p class="code-block leaderboard-superjump">
                
            </p>
            If you want to see where you stand in the leaderboard you can use the player centering url:
            <span class="inline-code">/leaderboard/{game}/player/{username/id}</span>
            For example, to get the players around you on the leaderboard of spleef use:
            <span class="inline-code">/leaderboard/spleef/player/Wouto1997</span>
            Which gives the following output:
            <p class="code-block leaderboard-spleef">
                
            </p>
        </div>

    </body>
</html>