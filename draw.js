// constants
var pathToLayers = "http://uncc.ath.cx/LayerCake/images/";
var gsLocation = "ws://localhost:8000/"
var sidepanelwidth = 120;
var borderwidth = 10;
var layerButtonHeight = 25
var TWO_PI = 6.283185307179586476
var DR_THRESHOLD = 0.01;

// properties of this layercake
var lcTitle = "Trike Monster";
var numLayers = 3;
var gameid = 26;
var interactionStyle = 2;
var controlled = 0;

// other
var x; //drawing context. short because I use it so damn much
var width;
var height;
var bgImage;
var layers = new Array();
var sims = new Array();
var thumbs = new Array();
var numLoaded;
var conn;
var live = false;
var begin = 0;
var simulationsOn = false;
var mine = null;
var dummy = null;
var nextSendTime = 0;
var rateLimit = 50;
var macicon;
var simulationsEnabled = false;
var mouseX = 0;
var mouseY = 0;
var oldx = 0;
var oldy = 0;
var oldpos = 0;
var serverclock = 0;
var clicked = false;


window.onload = function() {
    begin = millis();
	var drawingCanvas = document.getElementById('myDrawing');

    drawingCanvas.onmousedown = mousePressed;
    drawingCanvas.onmouseup = mouseReleased;
    drawingCanvas.onmousemove = mouseMoved;
    drawingCanvas.onkeydown = keyPressed;
	
	// Check the element is in the DOM and the browser supports canvas
	if(drawingCanvas && drawingCanvas.getContext) {
		// Initaliase a 2-dimensional drawing context
		x = drawingCanvas.getContext('2d');
        width = x.canvas.width;
        height = x.canvas.height;
        x.font = "italic bold 24px/60px Georgia, serif";
        numLoaded = 0;
		
        // clear screen and draw a status message
		clearBlack();
        whiteTextInTheMiddle( numLoaded+" out of "+(numLayers+1)+ " images loaded" );

        // load background image
        bgImage = new Image();
        bgImage.onload = imageLoaded;
        bgImage.src = pathToLayers + gameid + "/bg.png";

        // load little macintosh computer icon
        macicon = new Image();
        macicon.src = "http://uncc.ath.cx/LayerCake/macicon.png";

        // make a dummy layer for dead reckoning
        // physics are computed on this and when *mine* gets out of sync with it,
        // we send a pos/vel/acc update.
        dummy = new Layer();

        // create sims, but dont start them
		if (simulationsEnabled){
			sims = new array();;
			//load data for this interaction style
			sims[0] = new SimPlayer(millis(),0);    //FIX THESE
			sims[1] = new SimPlayer(millis(),1);
		}

        // load thumbnails
        var i;
        for (i=0; i<numLayers; i++){
			thumbs[i] = new Image()
            thumbs[i].src = pathToLayers + gameid + "/"+"thumb_"+(i+1)+".png";
	    }

        // preload images. When they all load, connectToGameServer() is called
        var imIndex;
        for (n=0; n<numLayers; n++) {
            layers[n] = new Layer();
            layers[n].image = new Image();
            layers[n].image.onload = imageLoaded;
            layers[n].image.src = pathToLayers + gameid + "/" + (n+1) + ".png";
        }
		
	}
}

function imageLoaded() {
    numLoaded++;
    clearBlack();

    // Print message
    whiteTextInTheMiddle( numLoaded+" out of "+(numLayers+1)+ " images loaded" );
    if (numLoaded == (numLayers+1)) {
        connectToGameServer();
    }
}

function connectToGameServer(){
    clearBlack();
    whiteTextInTheMiddle( "Connecting to game " );
    if("WebSocket" in window) {
        conn = new WebSocket(gsLocation);

        // set our message handling callback defined above
        conn.onmessage = gotMessage;

        conn.onopen = function(){
            clearBlack();
            whiteTextInTheMiddle( "Connected!" );
            conn.send("join-game:"+gameid);
            live = true
            setTimeout(startGame,600);
        };

        conn.onclose = function() {
            clearBlack();
            twoLineMiddle( "Can't connect to server,","but you can still play offline." );
            setTimeout(startGame,1400);
        };
        
        // in case of an error, do the same as above.
        conn.onerror = conn.onclose;

    } else {
        clearBlack();
        twoLineMiddle( "Your browser does not support WebSockets,","but you can still play offline." );
        setTimeout(startGame,1400);
    }

}

function startGame() {
    x.font = "14px sans-serif";
    setInterval(draw,50);
}

function draw() {
    clearBlack();
    
    //divider
    x.fillStyle = '#CCCCCC';
    x.fillRect(width-sidepanelwidth-borderwidth,0,10,height);

    thumbnail=null;

    //side panel
    x.lineWidth = 1;
    x.globalAlpha = 1;
    var a;
    for (a=0; a<numLayers; a++){
        hover = ((mouseX > (width-sidepanelwidth)) && (mouseY > a*25) && (mouseY < (a+1)*25));
        if (layers[a].owned){

			x.fillStyle = '#A0A0A0';
			x.strokeStyle = '#FFFFFF';
			x.fillRect(width-sidepanelwidth,a*layerButtonHeight,sidepanelwidth,layerButtonHeight);
			x.strokeRect(width-sidepanelwidth,a*layerButtonHeight,sidepanelwidth,layerButtonHeight);
			x.fillStyle = '#000000';
            x.fillText("layer "+(a+1)+" taken", width-sidepanelwidth+10,a*layerButtonHeight+20);

		} else {
			if (layers[a] != mine){
                
				if (hover){
                    x.fillStyle = '#E6E678'; //(230,230,120)
				} else {
                    x.fillStyle = '#F0F0F0'; //(240)
                }
                x.strokeStyle = '#FFFFFF';
				x.fillRect(width-sidepanelwidth,a*layerButtonHeight,sidepanelwidth,layerButtonHeight);
				x.strokeRect(width-sidepanelwidth,a*layerButtonHeight,sidepanelwidth,layerButtonHeight);
				x.fillStyle = '#000000';
				x.fillText("layer "+(a+1), width-sidepanelwidth+10,a*25+20);
				
                //noTint(); //////////////FIX
				if (layers[a].sim){
					x.drawImage(macicon, width-20, a*layerButtonHeight+8.5);
				}
			} else {
				x.fillStyle = '#FFFF00'; //(255,255,0)
				x.strokeStyle = '#FFFFFF';
			    x.fillRect(width-sidepanelwidth,a*layerButtonHeight,sidepanelwidth,layerButtonHeight);
			    x.strokeRect(width-sidepanelwidth,a*layerButtonHeight,sidepanelwidth,layerButtonHeight);
				x.fillStyle = '#000000';
				x.fillText("layer "+(a+1)+" mine",width-sidepanelwidth+10,a*layerButtonHeight+20);

                // little fill up pie
                radi = 8
                cenx = width-15;
                ceny = a*layerButtonHeight+layerButtonHeight/2
				x.strokeStyle = '#000000';
                x.beginPath(cenx+radi, ceny);
                x.moveTo();
                x.arc(cenx, ceny, radi, 0, TWO_PI);
                x.stroke();

				kk = map(mine.pos,0,1,0,TWO_PI);
				x.fillStyle = '#000000';
                x.beginPath();
                x.moveTo(cenx+radi, ceny);
                x.arc(width-15, a*layerButtonHeight+layerButtonHeight/2, radi, 0, kk);
                x.lineTo(cenx,ceny);
                x.fill();
			}
		}
		if (hover){
			thumbnail = thumbs[a];
		}
    }
    // robots button
    if (simulationsEnabled){
	    mouseOverButton = ((mouseX>width-sidepanelwidth) && (mouseY>height-20));
	    if (simulationsOn){
		    x.strokeStyle = '#C8FF64'; //(200,255,100)
		    if (mouseOverButton){
			    x.fillStyle = '#E6E65A'; //(230,230,90);
		    } else {
			    x.fillStyle = '#B4B45A'; //(180,180,90);
            }
            x.fillRect(width-sidepanelwidth,height-20,sidepanelwidth,20);
            x.strokeRect(width-sidepanelwidth,height-20,sidepanelwidth,20);
		    x.fillStyle = '#000000';
		    x.fillText("Robots On",width-sidepanelwidth+10,height-4);
	    } else {
		    x.strokeStyle = '#C8FF64'; //(200,255,100);
		    if (mouseOverButton)
			    x.fillStyle = '#E6E65A'; //(230,230,90);
		    else
			    x.fillStyle = '#646432'; //(100,100,50);
		    x.fillRect(width-sidepanelwidth,height-20,sidepanelwidth,20);
		    x.strokeRect(width-sidepanelwidth,height-20,sidepanelwidth,20);
		    x.fillStyle = '#000000';
		    x.fillText("Robots Off",width-sidepanelwidth+10,height-4);
	    }
    }
    // end side panel

    // layers  ////////////////////////////////////////////////////////////////// DRAW LAYERS
    bgw = bgImage.width;
	bgh = bgImage.height;

	// draw background image
    x.globalAlpha = 1;
	x.drawImage(bgImage,0,0);

    now = millis() - serverclock;

    if (interactionStyle==2){
        scrubPeriodic()
    }

    // update and draw each layer
    var a;
    for (a=0; a<numLayers; a++){
        lay = layers[a];
        lay.physics(now);

        if(controlled==0){
            //opacity
            x.globalAlpha = lay.pos;
            x.drawImage(lay.image,0,0);
        } else if (controlled==1){
            //brightness
            
        } else if (controlled==2){
            //blur
            
        } else {
            x.drawImage(lay.image,0,0);
        }
    }

	// if dummy is out of sync, send update
	if (mine != null && live){
		dummy.physics(now);
		if (abs(dummy.pos-mine.pos)>DR_THRESHOLD){
			if (sendAction()){;
				dummy.pos = mine.pos;
				dummy.vel = mine.vel;
				dummy.acc = mine.acc;
			}
		}
	}

	if (thumbnail!=null){
		x.drawImage(thumbnail, mouseX-thumbnail.width, mouseY);
		x.strokeStyle = '#646464';
		x.liseWidth = 3;
		x.strokeRect(mouseX-thumbnail.width, mouseY, thumbnail.width, thumbnail.height);
	}
    
}

function sendAction(){
	if (millis()>nextSendTime){
		conn.send( ("move:"+mine.pos.toString()+","+mine.vel.toString()+","+mine.acc.toString()+"\r") );
		nextSendTime = millis() + rateLimit;
		return true;
	} else {
		return false;
	}
}

function mousePressed(event){
    clicked = true;
    mouseX = event.offsetX;
    mouseY = event.offsetY;
    
    if (mouseX > width-sidepanelwidth){
	    if ((mouseX>width-sidepanelwidth) && (mouseY>height-20) && simulationsEnabled){
		    simulationsOn = !simulationsOn;
            var s;
		    for (s=0; s<sims.length; s++){
			    sims[s].dropIt();
		    }
	    } else {
            var a;
		    for (a=0; a<numLayers; a++){
			    if (!layers[a].owned){
				    if ((mouseY > a*25) && (mouseY < (a+1)*25)){
					    if (live){
						    conn.send( "request-layer:"+str(a)+"\r");
					    } else {
						    mine = layers[a];
					    }
				    }
			    }
		    }
	    }
    } else if (mine != null){
	    if (interactionStyle==0){
		    mine.up();
	    } else if (interactionStyle==1){
		    wave(false);
	    }
    }
}

function mouseReleased(event){
    clicked = false;
    mouseX = event.offsetX;
    mouseY = event.offsetY;

    if (!(mouseX > width-sidepanelwidth)){
        if (mine != null){
	        if (interactionStyle==0){
		        mine.down();
	        } else if (interactionStyle==1){
		        wave(false);
	        }
        }
    }
}

function mouseDragged(event){
    mouseX = event.offsetX;
    mouseY = event.offsetY;

    if (interactionStyle==1){
	    wave(true);
    }
}

function mouseMoved(event){
    if (clicked){
        mouseDragged(event);
        return;
    }

    mouseX = event.offsetX;
    mouseY = event.offsetY;
}

function keyPressed(event){

}

function scrubPeriodic(){
	// scrubbing (velocity based)
	if (mine!=null){
		mdist = dist(oldx,oldy,mouseX,mouseY);
		jchange = constrain(Math.abs(mdist)/100, 0,1);
		mine.scrub(jchange);
		oldx = mouseX;
		oldy = mouseY;
	}
}

function wave(contin){
	newpos = map(mouseY, height, 0, 0, 1);
	if (contin){
		mine.vel = (newpos-oldpos);
	} else {
		mine.vel = 0;
    }
	oldpos = newpos;
	mine.pos = newpos;
	mine.acc = 0;
}

function gotMessage(evt){
    m = evt.data;
    colon = m.indexOf(':',0);
    if (colon > 0){
        action = m.substring(0,colon);
        para = m.substring(colon+1,m.length).split(",");

        if (action=="layerstate"){
            lay = layers[para[0]];
            lay.owned = para[1]==1;

		    if (lay.owned){
			    if (simulationsOn){
				    lay.sim = false;
				    for (s=0; s<sims.length; s++)
					    if (sims[s].ownedLayer==lay)
						    sims[s].dropIt();
			    }
		    }

            lay.pos = parseFloat(para[2]);
			lay.vel = parseFloat(para[3]);
			lay.acc = parseFloat(para[4]);
			lay.lastup = parseFloat(para[5]);            
            lay.physics( millis() + serverclock );
    
        } else if (action=="layerown"){
            lay = layers[para[0]];
            lay.owned = para[1]==1;

			if (!lay.owned){
				if (interactionStyle==0){
					lay.down();
				} else if (interactionStyle==2){
					lay.scrub(0.0);
				}
			} else {
				if (simulationsOn){
					lay.sim = false;
					for (s=0; s<sims.length; s++)
						if (sims[s].ownedLayer==lay)
							sims[s].dropIt();
				}
			}

            draw();

        } else if (action=="servertime"){
            // time on the server clock approximately when this applet began
            serverclock = parseFloat(para[0]) - millis();

        } else if (action=="reown"){
            mine = layers[parseInt(para[0])];

			if (simulationsOn){
				mine.sim = false;
				for (s=0; s<sims.length; s++)
					if (sims[s].ownedLayer==mine)
						sims[s].dropIt();
			}
			
			if (interactionStyle==0){
				mine.down();
			}

			dummy.pos = mine.pos;
			dummy.vel = mine.vel;
			dummy.acc = mine.acc;
			dummy.lastup = mine.lastup;

            draw();

        } else if (action=="deny"){

        }
    }
}

function sendAction(){
	if (millis()>nextSendTime){
		conn.send( "move:"+str(mine.pos)+","+str(mine.vel)+","+str(mine.acc)+"\r");
		nextSendTime = millis() + rateLimit;
		return true;
	} else {
		return false;
	}
}

function millis(){
    return (new Date()).getTime() - begin;
}

function whiteTextInTheMiddle(text) {
    textWidth = x.measureText(text).width;
    x.fillStyle = "#FFFFFF";
    x.fillText(text, width/2-textWidth/2, height/2);
}

function twoLineMiddle(text1,text2) {
    textWidth = x.measureText(text1).width;
    x.fillStyle = "#FFFFFF";
    x.fillText(text1, width/2-textWidth/2, height/2-20);
    textWidth = x.measureText(text2).width;
    x.fillStyle = "#FFFFFF";
    x.fillText(text2, width/2-textWidth/2, height/2+20);
}

function clearBlack() {
	x.strokeStyle = "#000000";
	x.fillStyle = "#000000";
	x.fillRect(0, 0, width, height);
}



///////////////////////////////////////////     Processing functions        /////////////////////////////////////////
function constrain(x,A,B){
    if (x<A){
        return A;
    } else if (x>B) {
        return B;
    } else {
        return x;
    }
}

function map(x,a,b,c,d){
    return c+(((x-a)/(b-a))*(d-c));
}

function dist(a,b,c,d){
    return Math.sqrt(Math.pow((c-a),2)+Math.pow((d-b),2));
}














































