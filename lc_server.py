
""" WebSocket test resource.

This code will run a websocket resource on 8080 and reachable at ws://localhost:8080/test.
For compatibility with web-socket-js (a fallback to Flash for browsers that do not yet support
WebSockets) a policy server will also start on port 843.
See: http://github.com/gimite/web-socket-js
"""

__author__ = 'Reza Lotun'


from datetime import datetime
import sys
from time import time
from random import choice
import MySQLdb as mysql

from twisted.internet.protocol import Protocol, Factory
from twisted.web import resource
from twisted.web.static import File
from twisted.internet import task

from websocket import WebSocketHandler, WebSocketSite

sys.stdout = sys.stderr

credentials = open("dbcred").readlines()
dbuser = credentials[0][:-1]
dbpass = credentials[1][:-1]
bdname = credentials[2][:-1]

class Testhandler(WebSocketHandler):
    def __init__(self, transport):
        WebSocketHandler.__init__(self, transport)
        print "New Connection: %s"% (self.transport.getPeer())
        self.trusted = False
        self.joinedGame = None
        self.layerOwned = None
        site.clients.append(self)

    def send_time(self):
        # send current time as an ISO8601 string
        data = datetime.utcnow().isoformat().encode('utf8')
        self.transport.write(data)

    def frameReceived(self, frame):
        #if frame[-1]=='\r':
        #    frame = frame[:-1]
        print "received", repr(frame)
        if self.trusted:
            try:
                (action,para) = frame.split(':')
                if action == "join-game":
                    self.joinGame(para)
                elif action == "request-layer":
                    self.requestLayer(int(para))
                elif action == "move":
                    if self.joinedGame != None:
                        para = para.split(',')
                        self.move(float(para[0]),float(para[1]),float(para[2]))
                elif action == "player-count":
                    self.playerCount(para)
            except ValueError:
                self.message("Error: Invalid action message %r" % frame)
                return
        else:
            if frame=="oog1Theeaeb7po5HeiXie3oozefuu1EieiN5ji4lai0Ahy3see5Cie2":
                self.trusted = True
                print "trusting %s" % (self.transport.getPeer())
            else:
                print "Dropped potential Hacker %s"% (self.transport.getPeer())
                self.transport.loseConnection()

    def message(self, message):
        self.transport.write(message)

    def connectionLost(self, reason):
            print "Lost client %i because %s"% (id(self), str(reason))
            if self.joinedGame != None:
                self.joinedGame.players.remove(self)
            lay = self.layerOwned
            if lay != None:
                #self.move(0,0,0)
                lay.owner = None
                self.layerOwned = None
                for c in site.clients:
                    if c is not self:
                        if c.trusted:
                            c.message("layerown:%i,%i" % (lay.num, 0))
            site.clients.remove(self)

    def joinGame(self, gameid):
        game = None
        try:
            gamelist = site.games[gameid]
            for g in gamelist:
                if len(g.players) < len(g.layers):
                    game = g
                    break
            if game==None:
                print "creating new room for gameid %s" % gameid
                game = Game(gameid,len(gamelist[0].layers))
                gamelist.append(game)
        except KeyError:
            # if no games with this id exist, check database to see if this is a valid gameid
            conn = mysql.connect( host='localhost', user=dbuser, passwd=dbpass, db=dbname )
            cursor = conn.cursor()
            res = cursor.execute ("SELECT numLayers FROM cakes WHERE id=%s;" % gameid)
            if res==1:
                # if gameid is valid, create new game, and join it.
                row = cursor.fetchone()
                game = Game(gameid,row[0])
                site.games[gameid] = [game]
            else:
                # if invalid, send error message and drop connection.
                self.message("error: No Game with ID=%s exists" % gameid)
                self.transport.loseConnection()
            cursor.close()
            conn.close()
        # now we can assume all is well with game
        self.joinedGame = game
        game.players.append(self)
        # send player current time
        self.message("servertime:%f" % (time()) )
        # send player game state
        for l in game.layers:
            self.message("layerstate:%i,%i,%f,%f,%f,%f" % (l.num, int(l.owner!=None), l.position, l.velocity, l.acceleration, l.time))
        # assign player to layer
        layo = choice([lay for lay in game.layers if lay.owner==None])
        self.layerOwned = layo
        layo.owner = self
        self.message("reown:%i" % layo.num)
        # inform other players that layer is taken
        for other in self.joinedGame.players:
            if other != self:
                other.message("layerown:%i,%i" % (layo.num, 1))

    def requestLayer(self, layernum):
        try:
            prev = self.layerOwned
            dlayer = self.joinedGame.layers[layernum]
            if dlayer.owner == None:
                self.layerOwned = dlayer
                dlayer.owner = self
                # free the previously owned layer
                if prev != None:
                    prev.owner = None
                # tell requester that they got the new layer
                self.message("reown:%i" % layernum)
                # tell other players that the new layer is taken and the old layer is free
                for other in self.joinedGame.players:
                    if other != self:
                        other.message("layerown:%i,%i" % (dlayer.num, 1))
                        other.message("layerown:%i,%i" % (prev.num, 0))
            else:
                self.message("deny:")
        except IndexError:
            self.message("Error: layer number (%i) out of bounds" % layernum)

    def move(self, position, velocity, acceleration):
        lay = self.layerOwned
        lay.position = position
        lay.velocity = velocity
        lay.acceleration = acceleration
        lay.time = time()
        for other in self.joinedGame.players:
            if other != self:
                other.message("layerstate:%i,%i,%f,%f,%f,%f" % (lay.num, int(lay.owner!=None), lay.position, lay.velocity, lay.acceleration, lay.time))


class Game:
    def __init__(self,ig,lays):
        self.ident = ig
        self.players = []
        self.layers = [Layer(i) for i in range(lays)]

class Layer:
    def __init__(self,i):
        self.num = i
        self.owner = None
        self.position = 0.0
        self.velocity = 0.0
        self.acceleration = 0.0
        self.time = time()

if __name__ == "__main__":
    from twisted.internet import reactor

    # run our websocket server
    # serve index.html from the local directory
    root = File('.')
    site = WebSocketSite(root)
    site.addHandler('/', Testhandler)

    # store server variables as children of site
    site.clients = []
    site.games = {}

    reactor.listenTCP(8080, site)
    reactor.run()

