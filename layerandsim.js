//////////////////////////////////////////   Layer and SimPlayer classes   //////////////////////////////////////////
function Layer(){
    this.image = null;
    this.owned = false;
    this.sim = false;
    this.pos = 0.5;
    this.vel = 0;
    this.acc = 0;
    this.lastup = millis();
    this.newpos = 0;

    // chrome says Uncaught TypeError: Object #<an Object> has no method 'physics'
    // somebody aint doinit right.
    this.physics = function(now){
        //console.log("Layer Physics now "+now+" lastup "+this.lastup);
	    elapsed = (now-this.lastup)/1000; //speeds were designed for seconds
	    this.lastup = now;
	    if (interactionStyle==0){
		    this.vel = constrain(this.vel+elapsed*this.acc, -0.3, 0.3);
		    this.pos = this.pos+elapsed*this.vel;
	    } else if (interactionStyle==1){
		    this.vel = this.vel+elapsed*this.acc;
		    this.pos = this.pos+elapsed*this.vel;
	    } else if (interactionStyle==2){
		    this.pos = this.pos*0.9 + this.newpos*0.1;
	    }
	    this.pos = constrain(this.pos, 0, 1);
    }

    this.up = function(){
        this.acc = 0.9;
    }

    this.down = function(){
        this.acc = -0.9;
    }

    this.scrub = function(npos){
	    this.newpos = npos;
	    this.vel = 0;
	    this.acc = 0;
    }
}

function SimPlayer(now,whichDataset){
    this.ownedLayer = null;
    this.started = now;
    this.counter = 0;
    this.dataset = makeDataSet(whichDataset);
    this.nomore = false;
    this.oldscrubX = 0;
    this.oldscrubY = 0;
}

SimPlayer.prototype.act = function(now){
	// performs all the actions between where it left off and now
	// now should be the number of milliseconds since the applet started.
	// when the counter gets to the end of the data, act() should do nothing and notify the caller.
	if (this.nomore){
		if (random(200)<1){
			this.reset(now);
		} else {
			return;
        }
    }
	//try{  // figure out javascript catch error for this or do it with a break
            // loop should stop when it runs out of data
		while(this.dataset[this.counter][0]+this.started < now){
			this.perform(this.dataset[this.counter]);
			this.counter++;
		}
	//} catch(ArrayIndexOutOfBoundsException e){
	//	nomore = true;
	//}
}

SimPlayer.prototype.perform = function(h) {
	if (h[1]==0){ // pick new layer
		newlayer = pickFreeLayer();
		if (newlayer!=null){
            // only switch to new layer if there was at least,
            // one other layer free, otherwise keep what you got
			if (this.ownedLayer!=null){
				this.ownedLayer.sim = false;
				if (ineractionStyle==0){
					this.ownedLayer.down();
				} else if (ineractionStyle==2){
					this.ownedLayer.scrub(0);
				}
			}
			this.ownedLayer = newlayer;
			this.ownedLayer.sim = true;
		}
	} else if(h[1]==1){ // click at location
		if (this.ownedLayer!=null){
			if (ineractionStyle==0){
				this.ownedLayer.up();
			} else if (ineractionStyle==1){
				this.ownedLayer.pos = map(h[3], 518, 0, 0, 1);
			}
		}
	} else if(h[1]==2){ // unclick at location
		if (this.ownedLayer!=null){
			if (ineractionStyle==0){
				this.ownedLayer.down();
			} else if (ineractionStyle==1){
				this.ownedLayer.pos = map(h[3], 518, 0, 0, 1);
			}
		}
	} else if(h[1]==3){ // mouse dragged to location
		if (this.ownedLayer!=null){
			if (ineractionStyle==1){
				this.ownedLayer.pos = map(h[3], 518, 0, 0, 1);
			}
		}
	} else if(h[1]==4){ // mouse moved to location
		if (this.ownedLayer!=null){
			if (ineractionStyle==2){
				mdist = dist(this.oldscrubX,this.oldscrubY,h[2],h[3]);
				change = constrain(abs(mdist)/100, 0,1);
				this.ownedLayer.scrub(change);
				this.oldscrubX = h[2];
				this.oldscrubY = h[3];
			}
		}
	}
}


SimPlayer.prototype.reset = function(now){
	this.counter = 0;
	this.started = now;
	this.nomore = false;
}

SimPlayer.prototype.dropIt = function(){
	// tells the sim stop messing with layer because a human has it now
	this.nomore = true;
	if (this.ownedLayer==null)
		return;
	this.ownedLayer.sim = false;
	if (interactionStyle==0){
		this.ownedLayer.down();
	} else if (ineractionStyle==2){
		this.ownedLayer.scrub(0);
	}
}

