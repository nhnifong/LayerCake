var sys = require("sys")
  , http = require("http")
  , fs = require("fs")
  , path = require("path")
  , ws = require('./lib/ws/server');

var httpServer = http.createServer(function(req, res){
  if(req.method == "GET"){
    if( req.url.indexOf("favicon") > -1 ){
      res.writeHead(200, {'Content-Type': 'image/x-icon', 'Connection': 'close'});
      res.end("");
    } else {
      res.writeHead(200, {'Content-Type': 'text/html', 'Connection': 'close'});
      fs.createReadStream( path.normalize(path.join(__dirname, "chat.html")), {
        'flags': 'r',
        'encoding': 'binary',
        'mode': 0666,
        'bufferSize': 4 * 1024
      }).addListener("data", function(chunk){
        res.write(chunk, 'binary');
      }).addListener("end",function() {
        res.end();
      });
    }
  } else {
    res.writeHead(404);
    res.end();
  }
});


var server = ws.createServer({
  server: httpServer
});

server.addListener("listening", function(){
  sys.log("Listening for connections.");
});

// Handle WebSocket Requests
server.addListener("connection", function(conn){
  conn.username = "user_"+conn.id;
  conn.messageCount = 0;

  conn.send("** Connected as: user_"+conn.id);
  conn.send("** Type `/nick USERNAME` to change your username");

  conn.broadcast("** "+conn.username+" connected");

  conn.addListener("message", function(message){
    if(message[0] == "/"){
      // set username
      if((matches = message.match(/^\/nick (\w+)$/i)) && matches[1]){
        conn.username = matches[1];
        conn.send("** you are now known as: "+matches[1]);

      // get message count
      } else if(/^\/stats/.test(message)){
        conn.send("** you have sent "+conn.messageCount+" messages.");
      }
    } else {
      conn.messageCount++;
      server.broadcast(conn.username+": "+message);
    }
  });
});

server.addListener("close", function(conn){
  server.broadcast("<"+conn.id+"> disconnected");
});

server.listen(8000);
