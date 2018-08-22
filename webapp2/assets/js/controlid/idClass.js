/* Control iD - REP iDClass 
 *  */

//var repurl = 'https://192.168.99.59/';
var repurl = '';

/* Adição ao jQuery para permitir receber dados binários (sem conversão) */
$.ajaxTransport(
		"+binary",
		function(options, originalOptions, jqXHR) {
			// check for conditions and support for blob / arraybuffer
			// response type
			if (window.FormData
					&& ((options.dataType && (options.dataType == 'binary')) || (options.data && ((window.ArrayBuffer && options.data instanceof ArrayBuffer) || (window.Blob && options.data instanceof Blob))))) {
				return {
					// create new XMLHttpRequest
					send : function(_, callback) {
						// setup all variables

						var xhr = new XMLHttpRequest(), url = options.url, type = options.type,
						// blob or arraybuffer. Default is blob
						dataType = options.responseType || "blob", data = options.data
								|| null;

						xhr.addEventListener('load', function() {
							var data = {};
							data[options.dataType] = xhr.response;
							// make callback and send data
							callback(xhr.status, xhr.statusText, data,
									xhr.getAllResponseHeaders());
						});
						xhr.open(type, url, true);
						xhr.setRequestHeader("Content-Type",
								"application/json;charset=UTF-8");
						xhr.responseType = dataType;
						xhr.send(data);
					},
					abort : function() {
						jqXHR.abort();
					}
				};
			}
});

function getAFD(cTitle, data) {
	dvBlock = blockPage(cTitle);
	LoadObject('get_afd', data,
			function(result) {
				// md.setContent('Download concluído');
				console.log("Baixou AFD");
				teste1 = result;
				var resultView = new DataView(result);
				var filename = null;
				var header = null;
				// Guardar onde tem que cortar o arquivo
				var line_end = result.byteLength;
				if (line_end <= 0) {
					console.log(line_end);
					var md = new Modal();
					md.setTitle('Erro');
					md.setContent('Nenhum registro encontrado');
					md.addButton([ {
						'type' : 'cancel',
						'text' : 'OK'
					} ]);
					md.show();
					return;
				}
				for (var i = line_end - 1; i >= 1; i--) {
					// encontrou quebra de linha
					if (resultView.getInt8(i) == 10
							&& resultView.getInt8(i - 1) == 13) {
						// Achou nome do arquivo
						i--;
						if (filename == null) {
							filename = String.fromCharCode.apply(null,
									new Uint8Array(result
											.slice(i + 2, line_end)));
							line_end = i + 1;
							console.log(filename);
						}
						// Achou header
						else {
							header = new Uint8Array(result.slice(i + 2,
									line_end));
							line_end = i + 1;
							break;
						}
					}
				}

				// var superAFD = header + '\r\n' + result.substring(0,
				// posFilename).substring(0, posHeader);

				/*
				 * var fixedstring; superAFD = "éíó"; try{ // If the string is
				 * UTF-8, this will work and not throw an error.
				 * fixedstring=decodeURIComponent(escape(superAFD)); }catch(e){ //
				 * If it isn't, an error will be thrown, and we can asume that
				 * we have an ISO string. fixedstring=superAFD; }
				 */
				// var superAFD = result.afd.header + '\r\n' + result.afd.data +
				// result.afd.trailer + '\r\n' +result.afd.signature;
				var textFileAsBlob = header[0] != 13 ? new Blob([ header,
						result.slice(0, line_end - 1) ], {
					encoding : "ISO-8859-1",
					type : "text/plain; charset=ISO-8859-1"
				}) : new Blob([ result.slice(0, line_end - 1) ], {
					encoding : "ISO-8859-1",
					type : "text/plain; charset=ISO-8859-1"
				});

				try {
					var downloadLink = document.createElement("a");
					downloadLink.download = filename;
					downloadLink.innerHTML = "Download File";
					if (window.webkitURL != null) {
						// Chrome allows the link to be clicked
						// without actually adding it to the DOM.
						downloadLink.href = window.webkitURL
								.createObjectURL(textFileAsBlob);
					} else {
						// Firefox requires the link to be added to the DOM
						// before it can be clicked.
						downloadLink.href = window.URL
								.createObjectURL(textFileAsBlob);
						downloadLink.onclick = function() {
							try {
								downloadLink.remove();
							} catch (e) {
							}
						};
						downloadLink.style.display = "none";
						document.body.appendChild(downloadLink);
					}

					downloadLink.click();
				} catch (e) {
					window.navigator.msSaveBlob(textFileAsBlob, filename);
				}

				/*
				 */

				// md.addButton([
				// {
				// 'type' : 'cancel',
				// 'text' : 'OK'
				// }
				// ]);
			}, null, 0, dvBlock, true);
}

function SaveObject(title, commad, jsonData, finishCallback, count, dvBlock) {

	var errorValue = ''
	// console.log(jsonData)
	function checkJsonError(jData) {
		for ( var key in jData) {
			if (Array.isArray(jData[key])) {
				for (var i = 0; i < jData[key].length; i++)
					checkJsonError(jData[key][i]);
			} else {
				if (key === 'error')
					errorValue += jData[key] + '<br />';
				else if (typeof jData[key] === 'object'
						&& 'error' in jData[key])
					errorValue += jData[key].error + '<br />';
			}
		}
	}
	checkJsonError(jsonData);

	if (errorValue.length > 0) {
		console.log(errorValue);
		var md = new Modal();
		md.setTitle('Erro');
		md.setContent('Dados inválidos:' + errorValue);
		md.addButton([ {
			'type' : 'cancel',
			'text' : 'OK'
		} ]);
		md.show();
	} else {
		if (!dvBlock)
			dvBlock = blockPage('Aguarde, salvando dados');
		if (getCookie('session'))
			jsonData.session = getCookie('session');
		SendJSON(commad, jsonData, function(data) {
			if (checkSession(data, dvBlock, function(newCount) {
				SaveObject(title, commad, jsonData, finishCallback, newCount,
						dvBlock);
			}, count)) {
				unblockPage(dvBlock, function() {
					if (finishCallback)
						finishCallback()
				});
			} else if (data && 'error' in data) {
				var md = new Modal();
				md.setTitle(title);
				md.setContent('Ocorreu um erro:<br />');
				md.addContent(data.error);
				md.addButton([ {
					'type' : 'ok',
					'callback' : function() {
						md.hide();
					}
				} ]);
				md.show();
			}
		});
	}
}

function LoadObject(command, params, finishCallback, ajaxParams, count,
		dvBlock, rawReturnData, dontFadeOut) {
	if (!dvBlock)
		dvBlock = blockPage();
	if (getCookie('session'))
		params.session = getCookie('session');
	SendJSON(command, params, function(result) {
		console.log(result);
		if (checkSession(result, dvBlock, function(newCount) {
			LoadObject(command, params, finishCallback, ajaxParams, newCount,
					dvBlock);
		}, count)) {
			unblockPage(dvBlock, function() {
				if (finishCallback)
					finishCallback(result)
			}, dontFadeOut);
		} else if (result && result.error
				&& result.error.indexOf('Invalid session') == -1) {
			var md = new Modal();
			md.setContent('Ocorreu um erro:<br />');
			md.addContent(result.error);
			md.addButton([ {
				'type' : 'ok',
				'callback' : function() {
					md.hide();
				}
			} ]);
			md.show();
		}
	}, ajaxParams, rawReturnData);
}

function RemoveObject(command, params, finishCallback, ajaxParams, count,
		dvBlock) {
	if (!dvBlock)
		dvBlock = blockPage('Removendo...');
	if (getCookie('session'))
		params.session = getCookie('session');
	SendJSON(command, params, function(result) {
		if (checkSession(result, dvBlock, function(newCount) {
			LoadObject(command, params, finishCallback, ajaxParams, newCount,
					dvBlock);
		}, count)) {
			unblockPage(dvBlock, function() {
				if (finishCallback)
					finishCallback(result)
			});
		} else if (result && result.error
				&& result.error.indexOf('Invalid session') == -1) {
			var md = new Modal();
			md.setContent('Ocorreu um erro:<br />');
			md.addContent(result.error);
			md.addButton([ {
				'type' : 'ok',
				'callback' : function() {
					md.hide();
				}
			} ]);
			md.show();
		}
	}, ajaxParams);
}

function checkSession(data, dvBlock, callbackFail, count) {
	if (!count)
		count = 0;
	if (!data || (typeof data !== 'string' && 'error' in data)) {
		if (!data) {
			if (callbackFail && count < 3) {
				console.log('Tentativa ' + (count + 1) + 'de 3');
				callbackFail(count + 1);
			} else {
				blockPage('Não foi possível conectar ao equipamento', dvBlock);
				dvBlock.css({
					'cursor' : 'not-allowed'
				});
			}
		} else if (data.error.indexOf('Invalid session') == 0) {
			data = null;
			if (getCookie('session'))
				deleteCookie('session');
			blockPage('Sessão inválida', dvBlock);
			setTimeout(function() {
				unblockPage(dvBlock);
				window.location.hash = 'page=login';
				GetPage();
			}, 3000);

		} else
			unblockPage(dvBlock);
		return false;
	}
	return true;
}

function SendJSON(command, jsonData, finishCallback, ajaxParams, rawReturnData) {
	// console.log(jsonData);
	// return;
	// return {'error' : 'Invalid session'};
	var sendData;
	if (command.indexOf('.fcgi') < 0)
		command += '.fcgi';

	if (!ajaxParams || !('type' in ajaxParams)) {
		ajaxParams = {};
		ajaxParams.type = 'application/json; charset=ISO-8859-1';
		sendData = JSON.stringify(jsonData);
	} else
		sendData = jsonData;

	var ajaxParameters = {
		url : repurl + command,
		data : sendData,
		async : true,
		type : 'POST',
		contentType : ajaxParams.type,
		'success' : function(data) {
			console.log(command);
			console.log(data);
			console.log('----------------------------------');
			if (finishCallback)
				finishCallback(data);
		},
		'error' : function(jqXHR, textStatus, errorThrown) {
			if (finishCallback)
				finishCallback(jqXHR.responseJSON);
		},
	};
	// Raw return = dados binários que não desejamos converter
	if (rawReturnData) {
		ajaxParameters.processData = false;
		ajaxParameters.dataType = 'binary';
		ajaxParameters.responseType = 'arraybuffer';
	}
	$.ajax(ajaxParameters);
}

function SendBinary(command, binaryData, finishCallback, ajaxParams) {
	var sendData;
	if (command.indexOf('.fcgi') < 0)
		command += '.fcgi';

	ajaxParams = {};
	ajaxParams.type = 'application/octet-stream';
	sendData = binaryData;

	var ajaxParameters = {
		url : repurl + command,
		data : sendData,
		async : true,
		type : 'POST',
		processData : false,
		contentType : ajaxParams.type,
		'success' : function(data) {
			console.log(command);
			console.log(data);
			console.log('----------------------------------');
			if (finishCallback)
				finishCallback(data);
		},
		'error' : function(jqXHR, textStatus, errorThrown) {
			if (finishCallback)
				finishCallback(jqXHR.responseJSON);
		}
	};
	$.ajax(ajaxParameters);
}

function deleteCookie(cname) {
	setCookie(cname, '', 0);
}

function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	if (!exdays)
		exdays = 365;
	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
	var expires = "expires=" + d.toUTCString();
	document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ')
			c = c.substring(1);
		if (c.indexOf(name) != -1)
			return c.substring(name.length, c.length);
	}
	return null;
}