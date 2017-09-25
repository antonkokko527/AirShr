var ConnectionCheckInterval = 60000;

var WebSocketConnector = function (socketURL, onMessage, onConnect) {
	this.socketURL = socketURL;
	this.onMessage = onMessage;
	this.onConnect = onConnect;
	
	if (!"WebSocket" in window) {
		alert("Web socket is not supported in this browser. Please try to use another browser or you will not be able to see live information from server.");
	} else {
		this._initConnect();
	}
}

WebSocketConnector.prototype.forceNewConnection = function() {
	console.log('force new connection');
	this._initConnect();
}

WebSocketConnector.prototype._initValues = function() {
	
	if (this.webSocketObj) {
		this.webSocketObj.onopen = null;
		this.webSocketObj.onmessage = null;
		this.webSocketObj.onclose = null;
		this.webSocketObj.close();
	}
	
	this.webSocketObj = null;
	this.socketConnectionReady = false;
	if (this.checkTimeout) {
		clearTimeout(this.checkTimeout);
	}
	this.checkTimeout = null;
}

WebSocketConnector.prototype._initConnect = function() {
	var that = this;
	this._initValues();
	
	this.webSocketObj = new WebSocket(this.socketURL);
	
	this.webSocketObj.onopen = function(){
		console.log('connection openend.');
		that._checkConnect();
		that.onConnect();
	};
	
	this.webSocketObj.onmessage = function(event){
		if (event.data) that.onMessage(event.data);
		//send reply to server to keep the connection open
		//that.webSocketObj.send(JSON.stringify({"ping" : "pong"}));
	};
	
	this.webSocketObj.onclose = function(){
		console.log('connection closed. starting new connection.');
		that._initConnect();
	}
}


WebSocketConnector.prototype._checkConnect = function() {
	console.log('checking connection');
	var that = this;
	if (this.webSocketObj == null) {
		console.log('wow, null - init connect');
		that._initConnect();
		return;
	}
	
	if (this.webSocketObj.readyState == 3 || this.webSocketObj.readyState == 2) { // closing or closed, start new connection
		console.log('closed. reconnecting')
		that._initConnect();
		return;
	}
	
	if (that.webSocketObj.readyState == 1) {		// connection opened, ok.
		console.log('connection is good');
		that.socketConnectionReady = true;
	}
	
	this.checkTimeout = setTimeout(function(){
		that._checkConnect();
	}, ConnectionCheckInterval);
}
