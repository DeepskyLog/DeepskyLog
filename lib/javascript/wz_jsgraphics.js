/* This notice must be untouched at all times.

wz_jsgraphics.js    v. 3.05
The latest version is available at
http://www.walterzorn.com
or http://www.devira.com
or http://www.walterzorn.de

Copyright (c) 2002-2009 Walter Zorn. All rights reserved.
Created 3. 11. 2002 by Walter Zorn (Web: http://www.walterzorn.com )
Last modified: 2. 2. 2009

Performance optimizations for Internet Explorer
by Thomas Frank and John Holdsworth.
fillPolygon method implemented by Matthieu Haller.

High Performance JavaScript Graphics Library.
Provides methods
- to draw lines, rectangles, ellipses, polygons
	with specifiable line thickness,
- to fill rectangles, polygons, ellipses and arcs
- to draw text.
NOTE: Operations, functions and branching have rather been optimized
to efficiency and speed than to shortness of source code.

LICENSE: LGPL

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License (LGPL) as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA,
or see http://www.gnu.org/copyleft/lesser.html
*/


var jg_ok, jg_ie, jg_fast, jg_dom, jg_moz;


function _chkDHTM(wnd, x, i)
// Under XUL, owner of 'document' must be specified explicitly
{
	x = wnd.document.body || null;
	jg_ie = x && typeof x.insertAdjacentHTML != "undefined" && wnd.document.createElement;
	jg_dom = (x && !jg_ie &&
		typeof x.appendChild != "undefined" &&
		typeof wnd.document.createRange != "undefined" &&
		typeof (i = wnd.document.createRange()).setStartBefore != "undefined" &&
		typeof i.createContextualFragment != "undefined");
	jg_fast = jg_ie && wnd.document.all && !wnd.opera;
	jg_moz = jg_dom && typeof x.style.MozOpacity != "undefined";
	jg_ok = !!(jg_ie || jg_dom);
}

function _pntCnvDom()
{
	var x = this.wnd.document.createRange();
	x.setStartBefore(this.cnv);
	x = x.createContextualFragment(jg_fast? this._htmRpc() : this.htm);
	if(this.cnv) this.cnv.appendChild(x);
	this.htm = "";
}

function _pntCnvIe()
{
	if(this.cnv) this.cnv.insertAdjacentHTML("BeforeEnd", jg_fast? this._htmRpc() : this.htm);
	this.htm = "";
}

function _pntDoc()
{
	this.wnd.document.write(jg_fast? this._htmRpc() : this.htm);
	this.htm = '';
}

function _pntN()
{
	;
}

function _mkDiv(x, y, w, h)
{
	this.htm += '<div style="position:absolute;'+
		'left:' + x + 'px;'+
		'top:' + y + 'px;'+
		'width:' + w + 'px;'+
		'height:' + h + 'px;'+
		'clip:rect(0,'+w+'px,'+h+'px,0);'+
		'background-color:' + this.color +
		(!jg_moz? ';overflow:hidden' : '')+
		';"><\/div>';
}

function _mkDivIe(x, y, w, h)
{
	this.htm += '%%'+this.color+';'+x+';'+y+';'+w+';'+h+';';
}

function _mkDivPrt(x, y, w, h)
{
	this.htm += '<div style="position:absolute;'+
		'border-left:' + w + 'px solid ' + this.color + ';'+
		'left:' + x + 'px;'+
		'top:' + y + 'px;'+
		'width:0px;'+
		'height:' + h + 'px;'+
		'clip:rect(0,'+w+'px,'+h+'px,0);'+
		'background-color:' + this.color +
		(!jg_moz? ';overflow:hidden' : '')+
		';"><\/div>';
}

var _regex =  /%%([^;]+);([^;]+);([^;]+);([^;]+);([^;]+);/g;
function _htmRpc()
{
	return this.htm.replace(
		_regex,
		'<div style="overflow:hidden;position:absolute;background-color:'+
		'$1;left:$2px;top:$3px;width:$4px;height:$5px"></div>\n');
}

function _htmPrtRpc()
{
	return this.htm.replace(
		_regex,
		'<div style="overflow:hidden;position:absolute;background-color:'+
		'$1;left:$2px;top:$3px;width:$4px;height:$5px;border-left:$4px solid $1"></div>\n');
}

function _mkLin(x1, y1, x2, y2)
{
	if(x1 > x2)
	{
		var _x2 = x2;
		var _y2 = y2;
		x2 = x1;
		y2 = y1;
		x1 = _x2;
		y1 = _y2;
	}
	var dx = x2-x1, dy = Math.abs(y2-y1),
	x = x1, y = y1,
	yIncr = (y1 > y2)? -1 : 1;

	if(dx >= dy)
	{
		var pr = dy<<1,
		pru = pr - (dx<<1),
		p = pr-dx,
		ox = x;
		while(dx > 0)
		{--dx;
			++x;
			if(p > 0)
			{
				this._mkDiv(ox, y, x-ox, 1);
				y += yIncr;
				p += pru;
				ox = x;
			}
			else p += pr;
		}
		this._mkDiv(ox, y, x2-ox+1, 1);
	}

	else
	{
		var pr = dx<<1,
		pru = pr - (dy<<1),
		p = pr-dy,
		oy = y;
		if(y2 <= y1)
		{
			while(dy > 0)
			{--dy;
				if(p > 0)
				{
					this._mkDiv(x++, y, 1, oy-y+1);
					y += yIncr;
					p += pru;
					oy = y;
				}
				else
				{
					y += yIncr;
					p += pr;
				}
			}
			this._mkDiv(x2, y2, 1, oy-y2+1);
		}
		else
		{
			while(dy > 0)
			{--dy;
				y += yIncr;
				if(p > 0)
				{
					this._mkDiv(x++, oy, 1, y-oy);
					p += pru;
					oy = y;
				}
				else p += pr;
			}
			this._mkDiv(x2, oy, 1, y2-oy+1);
		}
	}
}

function _mkLin2D(x1, y1, x2, y2)
{
	if(x1 > x2)
	{
		var _x2 = x2;
		var _y2 = y2;
		x2 = x1;
		y2 = y1;
		x1 = _x2;
		y1 = _y2;
	}
	var dx = x2-x1, dy = Math.abs(y2-y1),
	x = x1, y = y1,
	yIncr = (y1 > y2)? -1 : 1;

	var s = this.stroke;
	if(dx >= dy)
	{
		if(dx > 0 && s-3 > 0)
		{
			var _s = (s*dx*Math.sqrt(1+dy*dy/(dx*dx))-dx-(s>>1)*dy) / dx;
			_s = (!(s-4)? Math.ceil(_s) : Math.round(_s)) + 1;
		}
		else var _s = s;
		var ad = Math.ceil(s/2);

		var pr = dy<<1,
		pru = pr - (dx<<1),
		p = pr-dx,
		ox = x;
		while(dx > 0)
		{--dx;
			++x;
			if(p > 0)
			{
				this._mkDiv(ox, y, x-ox+ad, _s);
				y += yIncr;
				p += pru;
				ox = x;
			}
			else p += pr;
		}
		this._mkDiv(ox, y, x2-ox+ad+1, _s);
	}

	else
	{
		if(s-3 > 0)
		{
			var _s = (s*dy*Math.sqrt(1+dx*dx/(dy*dy))-(s>>1)*dx-dy) / dy;
			_s = (!(s-4)? Math.ceil(_s) : Math.round(_s)) + 1;
		}
		else var _s = s;
		var ad = Math.round(s/2);

		var pr = dx<<1,
		pru = pr - (dy<<1),
		p = pr-dy,
		oy = y;
		if(y2 <= y1)
		{
			++ad;
			while(dy > 0)
			{--dy;
				if(p > 0)
				{
					this._mkDiv(x++, y, _s, oy-y+ad);
					y += yIncr;
					p += pru;
					oy = y;
				}
				else
				{
					y += yIncr;
					p += pr;
				}
			}
			this._mkDiv(x2, y2, _s, oy-y2+ad);
		}
		else
		{
			while(dy > 0)
			{--dy;
				y += yIncr;
				if(p > 0)
				{
					this._mkDiv(x++, oy, _s, y-oy+ad);
					p += pru;
					oy = y;
				}
				else p += pr;
			}
			this._mkDiv(x2, oy, _s, y2-oy+ad+1);
		}
	}
}

function _mkLinDott(x1, y1, x2, y2)
{
	if(x1 > x2)
	{
		var _x2 = x2;
		var _y2 = y2;
		x2 = x1;
		y2 = y1;
		x1 = _x2;
		y1 = _y2;
	}
	var dx = x2-x1, dy = Math.abs(y2-y1),
	x = x1, y = y1,
	yIncr = (y1 > y2)? -1 : 1,
	drw = true;
	if(dx >= dy)
	{
		var pr = dy<<1,
		pru = pr - (dx<<1),
		p = pr-dx;
		while(dx > 0)
		{--dx;
			if(drw) this._mkDiv(x, y, 1, 1);
			drw = !drw;
			if(p > 0)
			{
				y += yIncr;
				p += pru;
			}
			else p += pr;
			++x;
		}
	}
	else
	{
		var pr = dx<<1,
		pru = pr - (dy<<1),
		p = pr-dy;
		while(dy > 0)
		{--dy;
			if(drw) this._mkDiv(x, y, 1, 1);
			drw = !drw;
			y += yIncr;
			if(p > 0)
			{
				++x;
				p += pru;
			}
			else p += pr;
		}
	}
	if(drw) this._mkDiv(x, y, 1, 1);
}

function _mkOv(left, top, width, height)
{
	var a = (++width)>>1, b = (++height)>>1,
	wod = width&1, hod = height&1,
	cx = left+a, cy = top+b,
	x = 0, y = b,
	ox = 0, oy = b,
	aa2 = (a*a)<<1, aa4 = aa2<<1, bb2 = (b*b)<<1, bb4 = bb2<<1,
	st = (aa2>>1)*(1-(b<<1)) + bb2,
	tt = (bb2>>1) - aa2*((b<<1)-1),
	w, h;
	while(y > 0)
	{
		if(st < 0)
		{
			st += bb2*((x<<1)+3);
			tt += bb4*(++x);
		}
		else if(tt < 0)
		{
			st += bb2*((x<<1)+3) - aa4*(y-1);
			tt += bb4*(++x) - aa2*(((y--)<<1)-3);
			w = x-ox;
			h = oy-y;
			if((w&2) && (h&2))
			{
				this._mkOvQds(cx, cy, x-2, y+2, 1, 1, wod, hod);
				this._mkOvQds(cx, cy, x-1, y+1, 1, 1, wod, hod);
			}
			else this._mkOvQds(cx, cy, x-1, oy, w, h, wod, hod);
			ox = x;
			oy = y;
		}
		else
		{
			tt -= aa2*((y<<1)-3);
			st -= aa4*(--y);
		}
	}
	w = a-ox+1;
	h = (oy<<1)+hod;
	y = cy-oy;
	this._mkDiv(cx-a, y, w, h);
	this._mkDiv(cx+ox+wod-1, y, w, h);
}

function _mkOvTilt(cx, cy, wx, hy, angle)
{	var a=(++wx)>>1,
	    b=(++hy)>>1;
	var aa=a*a,
	    bb=b*b,
			cosangle=Math.cos(angle),
			sinangle=Math.sin(angle),
			A=aa-((aa-bb)*cosangle*cosangle),
			B=aa-((aa-bb)*sinangle*sinangle),
			C=-(aa-bb)*sinangle*cosangle;
			F=-aa*bb;
	var xt =-Math.round(C/Math.sqrt(A)),
	    yt = Math.round(Math.sqrt(A)),
			xtr= Math.round((B-C)/Math.sqrt(A+B-(2*C))),
			ytr= Math.round((A-C)/Math.sqrt(A+B-(2*C))),
			xr = Math.round(Math.sqrt(B)),
			yr =-Math.round(C/Math.sqrt(B)),
			xrb= Math.round((B+C)/Math.sqrt(A+B+(2*C))),
			yrb=-Math.round((A+C)/Math.sqrt(A+B+(2*C))),
			xb = Math.round(C/Math.sqrt(A)),
			yb =-Math.round(Math.sqrt(A));
	var x=xt,y=yt,h=0,w=0,d=-1;
	while((x)<xtr)
	{ while((d<=0)&&(((xtr-x-w)>0)||((y-ytr)>1)))
	  { w++;
		  d=(A*(x+w)*(x+w))+(B*(y-.5)*(y-.5))+2*C*(x+w)*(y-.5)+F;
		}
	  this._mkDiv(cx+x,cy+y,w,1);
	  this._mkDiv(cx-x-w+1,cy-y,w,1);
		y--,x+=w,w=0,d=-1;
	}
  x=xtr,y=ytr,h=0,w=0,d=1;
	while(y>=yr)
	{ while((d>0)&&(((y-h-yr)>=0)||((xr-x)>1)))
	  { h++;
		  d=(A*(x+.5)*(x+.5))+(B*(y-h)*(y-h))+2*C*(x+.5)*(y-h)+F;
		}
	  this._mkDiv(cx+x,cy+y-h+1,1,h);
	  this._mkDiv(cx-x,cy-y,1,h);
		x++,y-=h,h=0,d=1;
	}
  x=xr,y=yr,h=0,w=0,d=-1;
	while((y)>yrb)
	{ while((d<=0)&&(((y-h-yrb)>0)||((x-xrb)>1)))
	  { h++;
		  d=(A*(x-.5)*(x-.5))+(B*(y-h)*(y-h))+2*C*(x-.5)*(y-h)+F;
		}
	  this._mkDiv(cx+x,cy+y-h+1,1,h);
	  this._mkDiv(cx-x,cy-y,1,h);
		x--,y-=h,h=0,d=-1;
	}
	x=xrb,y=yrb,h=0,w=0,d=1;
	while((x)>xb)
	{while((d>=0)&&(((x-w-xb)>0)||((y-yb)>1)))
	  { w++;
		  d=(A*(x-w)*(x-w))+(B*(y-.5)*(y-.5))+2*C*(x-w)*(y-.5)+F;
		}
	  this._mkDiv(cx+x-w+1,cy+y,w,1);
	  this._mkDiv(cx-x,cy-y,w,1);
		y--,x-=w,w=0,d=1;
	}
}

function _mkOvTiltLimited(cx, cy, wx, hy, angle, lx, rx, ty, by)
{	var a=(++wx)>>1,
	    b=(++hy)>>1;
	var aa=a*a,
	    bb=b*b,
			cosangle=Math.cos(angle),
			sinangle=Math.sin(angle),
			A=aa-((aa-bb)*cosangle*cosangle),
			B=aa-((aa-bb)*sinangle*sinangle),
			C=-(aa-bb)*sinangle*cosangle;
			F=-aa*bb,
			xt =-Math.round(C/Math.sqrt(A)),
	    yt = Math.round(Math.sqrt(A)),
			xtr= Math.round((B-C)/Math.sqrt(A+B-(2*C))),
			ytr= Math.round((A-C)/Math.sqrt(A+B-(2*C))),
			xr = Math.round(Math.sqrt(B)),
			yr =-Math.round(C/Math.sqrt(B)),
			xrb= Math.round((B+C)/Math.sqrt(A+B+(2*C))),
			yrb=-Math.round((A+C)/Math.sqrt(A+B+(2*C))),
			xb = Math.round(C/Math.sqrt(A)),
			yb =-Math.round(Math.sqrt(A));
	var x=xt,y=yt,h=0,w=0,d=-1;
	while((x)<xtr)
	{ while((d<=0)&&(((xtr-x-w)>0)||((y-ytr)>1)))
	  { w++;
		  d=(A*(x+w)*(x+w))+(B*(y-.5)*(y-.5))+2*C*(x+w)*(y-.5)+F;
		}
	  if(((cx+x)>lx)&&((cx+x+w)<rx)&&((cy+y)<ty)&&((cy+y)>by))
		  this._mkDiv(cx+x,cy+y,w,1);
	  if(((cx-x-w+1)>lx)&&((cx-x+1)<rx)&&((cy-y)<ty)&&((cy-y)>by))
	    this._mkDiv(cx-x-w+1,cy-y,w,1);
		y--,x+=w,w=0,d=-1;
	}
  x=xtr,y=ytr,h=0,w=0,d=1;
	while(y>=yr)
	{ while((d>0)&&(((y-h-yr)>=0)||((xr-x)>1)))
	  { h++;
		  d=(A*(x+.5)*(x+.5))+(B*(y-h)*(y-h))+2*C*(x+.5)*(y-h)+F;
		}
	  if(((cx+x)>lx)&&((cx+x)<rx)&&((cy+y)<ty)&&((cy+y-h)>by))
  	  this._mkDiv(cx+x,cy+y-h+1,1,h);
	  if(((cx-x)>lx)&&((cx-x)<rx)&&((cy-y+h)<ty)&&((cy-y)>by))
  	  this._mkDiv(cx-x,cy-y,1,h);
		x++,y-=h,h=0,d=1;
	}
  x=xr,y=yr,h=0,w=0,d=-1;
	while((y)>yrb)
	{ while((d<=0)&&(((y-h-yrb)>0)||((x-xrb)>1)))
	  { h++;
		  d=(A*(x-.5)*(x-.5))+(B*(y-h)*(y-h))+2*C*(x-.5)*(y-h)+F;
		}
	  if(((cx+x)>lx)&&((cx+x)<rx)&&((cy+y)<ty)&&((cy+y-h)>by))
  	  this._mkDiv(cx+x,cy+y-h+1,1,h);
	  if(((cx-x)>lx)&&((cx-x)<rx)&&((cy-y+h)<ty)&&((cy-y)>by))
  	  this._mkDiv(cx-x,cy-y,1,h);
		x--,y-=h,h=0,d=-1;
	}
	x=xrb,y=yrb,h=0,w=0,d=1;
	while((x)>xb)
	{ while((d>=0)&&(((x-w-xb)>0)||((y-yb)>1)))
	  { w++;
		  d=(A*(x-w)*(x-w))+(B*(y-.5)*(y-.5))+2*C*(x-w)*(y-.5)+F;
		}
	  if(((cx+x-w+1)>lx)&&((cx+x+1)<rx)&&((cy+y)<ty)&&((cy+y)>by))
  	  this._mkDiv(cx+x-w+1,cy+y,w,1);
	  if(((cx-x)>lx)&&((cx-x+w)<rx)&&((cy-y)<ty)&&((cy-y)>by))
  	  this._mkDiv(cx-x,cy-y,w,1);
		y--,x-=w,w=0,d=1;
	}
}

function _mkOv2D(left, top, width, height)
{
	var s = this.stroke;
	width += s+1;
	height += s+1;
	var a = width>>1, b = height>>1,
	wod = width&1, hod = height&1,
	cx = left+a, cy = top+b,
	x = 0, y = b,
	aa2 = (a*a)<<1, aa4 = aa2<<1, bb2 = (b*b)<<1, bb4 = bb2<<1,
	st = (aa2>>1)*(1-(b<<1)) + bb2,
	tt = (bb2>>1) - aa2*((b<<1)-1);

	if(s-4 < 0 && (!(s-2) || width-51 > 0 && height-51 > 0))
	{
		var ox = 0, oy = b,
		w, h,
		pxw;
		while(y > 0)
		{
			if(st < 0)
			{
				st += bb2*((x<<1)+3);
				tt += bb4*(++x);
			}
			else if(tt < 0)
			{
				st += bb2*((x<<1)+3) - aa4*(y-1);
				tt += bb4*(++x) - aa2*(((y--)<<1)-3);
				w = x-ox;
				h = oy-y;

				if(w-1)
				{
					pxw = w+1+(s&1);
					h = s;
				}
				else if(h-1)
				{
					pxw = s;
					h += 1+(s&1);
				}
				else pxw = h = s;
				this._mkOvQds(cx, cy, x-1, oy, pxw, h, wod, hod);
				ox = x;
				oy = y;
			}
			else
			{
				tt -= aa2*((y<<1)-3);
				st -= aa4*(--y);
			}
		}
		this._mkDiv(cx-a, cy-oy, s, (oy<<1)+hod);
		this._mkDiv(cx+a+wod-s, cy-oy, s, (oy<<1)+hod);
	}

	else
	{
		var _a = (width-(s<<1))>>1,
		_b = (height-(s<<1))>>1,
		_x = 0, _y = _b,
		_aa2 = (_a*_a)<<1, _aa4 = _aa2<<1, _bb2 = (_b*_b)<<1, _bb4 = _bb2<<1,
		_st = (_aa2>>1)*(1-(_b<<1)) + _bb2,
		_tt = (_bb2>>1) - _aa2*((_b<<1)-1),

		pxl = new Array(),
		pxt = new Array(),
		_pxb = new Array();
		pxl[0] = 0;
		pxt[0] = b;
		_pxb[0] = _b-1;
		while(y > 0)
		{
			if(st < 0)
			{
				pxl[pxl.length] = x;
				pxt[pxt.length] = y;
				st += bb2*((x<<1)+3);
				tt += bb4*(++x);
			}
			else if(tt < 0)
			{
				pxl[pxl.length] = x;
				st += bb2*((x<<1)+3) - aa4*(y-1);
				tt += bb4*(++x) - aa2*(((y--)<<1)-3);
				pxt[pxt.length] = y;
			}
			else
			{
				tt -= aa2*((y<<1)-3);
				st -= aa4*(--y);
			}

			if(_y > 0)
			{
				if(_st < 0)
				{
					_st += _bb2*((_x<<1)+3);
					_tt += _bb4*(++_x);
					_pxb[_pxb.length] = _y-1;
				}
				else if(_tt < 0)
				{
					_st += _bb2*((_x<<1)+3) - _aa4*(_y-1);
					_tt += _bb4*(++_x) - _aa2*(((_y--)<<1)-3);
					_pxb[_pxb.length] = _y-1;
				}
				else
				{
					_tt -= _aa2*((_y<<1)-3);
					_st -= _aa4*(--_y);
					_pxb[_pxb.length-1]--;
				}
			}
		}

		var ox = -wod, oy = b,
		_oy = _pxb[0],
		l = pxl.length,
		w, h;
		for(var i = 0; i < l; i++)
		{
			if(typeof _pxb[i] != "undefined")
			{
				if(_pxb[i] < _oy || pxt[i] < oy)
				{
					x = pxl[i];
					this._mkOvQds(cx, cy, x, oy, x-ox, oy-_oy, wod, hod);
					ox = x;
					oy = pxt[i];
					_oy = _pxb[i];
				}
			}
			else
			{
				x = pxl[i];
				this._mkDiv(cx-x, cy-oy, 1, (oy<<1)+hod);
				this._mkDiv(cx+ox+wod, cy-oy, 1, (oy<<1)+hod);
				ox = x;
				oy = pxt[i];
			}
		}
		this._mkDiv(cx-a, cy-oy, 1, (oy<<1)+hod);
		this._mkDiv(cx+ox+wod, cy-oy, 1, (oy<<1)+hod);
	}
}

function _mkOvDott(left, top, width, height)
{
	var a = (++width)>>1, b = (++height)>>1,
	wod = width&1, hod = height&1, hodu = hod^1,
	cx = left+a, cy = top+b,
	x = 0, y = b,
	aa2 = (a*a)<<1, aa4 = aa2<<1, bb2 = (b*b)<<1, bb4 = bb2<<1,
	st = (aa2>>1)*(1-(b<<1)) + bb2,
	tt = (bb2>>1) - aa2*((b<<1)-1),
	drw = true;
	while(y > 0)
	{
		if(st < 0)
		{
			st += bb2*((x<<1)+3);
			tt += bb4*(++x);
		}
		else if(tt < 0)
		{
			st += bb2*((x<<1)+3) - aa4*(y-1);
			tt += bb4*(++x) - aa2*(((y--)<<1)-3);
		}
		else
		{
			tt -= aa2*((y<<1)-3);
			st -= aa4*(--y);
		}
		if(drw && y >= hodu) this._mkOvQds(cx, cy, x, y, 1, 1, wod, hod);
		drw = !drw;
	}
}

function _mkRect(x, y, w, h)
{
	var s = this.stroke;
	this._mkDiv(x, y, w, s);
	this._mkDiv(x+w, y, s, h);
	this._mkDiv(x, y+h, w+s, s);
	this._mkDiv(x, y+s, s, h-s);
}

function _mkRectDott(x, y, w, h)
{
	this.drawLine(x, y, x+w, y);
	this.drawLine(x+w, y, x+w, y+h);
	this.drawLine(x, y+h, x+w, y+h);
	this.drawLine(x, y, x, y+h);
}

function jsgFont()
{
	this.PLAIN = 'font-weight:normal;';
	this.BOLD = 'font-weight:bold;';
	this.ITALIC = 'font-style:italic;';
	this.ITALIC_BOLD = this.ITALIC + this.BOLD;
	this.BOLD_ITALIC = this.ITALIC_BOLD;
}
var Font = new jsgFont();

function jsgStroke()
{
	this.DOTTED = -1;
}
var Stroke = new jsgStroke();

function jsGraphics(cnv, wnd)
{
	this.setColor = function(x)
	{
		this.color = x.toLowerCase();
	};

	this.setStroke = function(x)
	{
		this.stroke = x;
		if(!(x+1))
		{
			this.drawLine = _mkLinDott;
			this._mkOv = _mkOvDott;
			this.drawRect = _mkRectDott;
		}
		else if(x-1 > 0)
		{
			this.drawLine = _mkLin2D;
			this._mkOv = _mkOv2D;
			this.drawRect = _mkRect;
		}
		else
		{
			this.drawLine = _mkLin;
			this._mkOv = _mkOv;
			this.drawRect = _mkRect;
		}
		this._mkOvTilt = _mkOvTilt;
		this._mkOvTiltLimited = _mkOvTiltLimited;
	};

	this.setPrintable = function(arg)
	{
		this.printable = arg;
		if(jg_fast)
		{
			this._mkDiv = _mkDivIe;
			this._htmRpc = arg? _htmPrtRpc : _htmRpc;
		}
		else this._mkDiv = arg? _mkDivPrt : _mkDiv;
	};

	this.setFont = function(fam, sz, sty)
	{
		this.ftFam = fam;
		this.ftSz = sz;
		this.ftSty = sty || Font.PLAIN;
	};

	this.drawPolyline = this.drawPolyLine = function(x, y)
	{
		for (var i=x.length - 1; i;)
		{--i;
			this.drawLine(x[i], y[i], x[i+1], y[i+1]);
		}
	};

	this.fillRect = function(x, y, w, h)
	{
		this._mkDiv(x, y, w, h);
	};

	this.drawPolygon = function(x, y)
	{
		this.drawPolyline(x, y);
		this.drawLine(x[x.length-1], y[x.length-1], x[0], y[0]);
	};

	this.drawEllipse = this.drawOval = function(x, y, w, h)
	{
		this._mkOv(x, y, w, h);
	};
	
	this.drawEllipseTilt = this.drawOvalTilt = function(x, y, w, h, a)
	{ 
		this._mkOvTilt(x, y, w, h, a);
	};

	this.drawEllipseTiltLimited = this.drawOvalTiltLimited = function(x, y, w, h, a, lx, rx, ty, by)
	{ 
		this._mkOvTiltLimited(x, y, w, h, a, lx, rx, ty, by);
	};

	this.fillEllipse = this.fillOval = function(left, top, w, h)
	{
		var a = w>>1, b = h>>1,
		wod = w&1, hod = h&1,
		cx = left+a, cy = top+b,
		x = 0, y = b, oy = b,
		aa2 = (a*a)<<1, aa4 = aa2<<1, bb2 = (b*b)<<1, bb4 = bb2<<1,
		st = (aa2>>1)*(1-(b<<1)) + bb2,
		tt = (bb2>>1) - aa2*((b<<1)-1),
		xl, dw, dh;
		if(w) while(y > 0)
		{
			if(st < 0)
			{
				st += bb2*((x<<1)+3);
				tt += bb4*(++x);
			}
			else if(tt < 0)
			{
				st += bb2*((x<<1)+3) - aa4*(y-1);
				xl = cx-x;
				dw = (x<<1)+wod;
				tt += bb4*(++x) - aa2*(((y--)<<1)-3);
				dh = oy-y;
				this._mkDiv(xl, cy-oy, dw, dh);
				this._mkDiv(xl, cy+y+hod, dw, dh);
				oy = y;
			}
			else
			{
				tt -= aa2*((y<<1)-3);
				st -= aa4*(--y);
			}
		}
		this._mkDiv(cx-a, cy-oy, w, (oy<<1)+hod);
	};

	this.fillArc = function(iL, iT, iW, iH, fAngA, fAngZ)
	{
		var a = iW>>1, b = iH>>1,
		iOdds = (iW&1) | ((iH&1) << 16),
		cx = iL+a, cy = iT+b,
		x = 0, y = b, ox = x, oy = y,
		aa2 = (a*a)<<1, aa4 = aa2<<1, bb2 = (b*b)<<1, bb4 = bb2<<1,
		st = (aa2>>1)*(1-(b<<1)) + bb2,
		tt = (bb2>>1) - aa2*((b<<1)-1),
		// Vars for radial boundary lines
		xEndA, yEndA, xEndZ, yEndZ,
		iSects = (1 << (Math.floor((fAngA %= 360.0)/180.0) << 3))
				| (2 << (Math.floor((fAngZ %= 360.0)/180.0) << 3))
				| ((fAngA >= fAngZ) << 16),
		aBndA = new Array(b+1), aBndZ = new Array(b+1);
		
		// Set up radial boundary lines
		fAngA *= Math.PI/180.0;
		fAngZ *= Math.PI/180.0;
		xEndA = cx+Math.round(a*Math.cos(fAngA));
		yEndA = cy+Math.round(-b*Math.sin(fAngA));
		_mkLinVirt(aBndA, cx, cy, xEndA, yEndA);
		xEndZ = cx+Math.round(a*Math.cos(fAngZ));
		yEndZ = cy+Math.round(-b*Math.sin(fAngZ));
		_mkLinVirt(aBndZ, cx, cy, xEndZ, yEndZ);

		while(y > 0)
		{
			if(st < 0) // Advance x
			{
				st += bb2*((x<<1)+3);
				tt += bb4*(++x);
			}
			else if(tt < 0) // Advance x and y
			{
				st += bb2*((x<<1)+3) - aa4*(y-1);
				ox = x;
				tt += bb4*(++x) - aa2*(((y--)<<1)-3);
				this._mkArcDiv(ox, y, oy, cx, cy, iOdds, aBndA, aBndZ, iSects);
				oy = y;
			}
			else // Advance y
			{
				tt -= aa2*((y<<1)-3);
				st -= aa4*(--y);
				if(y && (aBndA[y] != aBndA[y-1] || aBndZ[y] != aBndZ[y-1]))
				{
					this._mkArcDiv(x, y, oy, cx, cy, iOdds, aBndA, aBndZ, iSects);
					ox = x;
					oy = y;
				}
			}
		}
		this._mkArcDiv(x, 0, oy, cx, cy, iOdds, aBndA, aBndZ, iSects);
		if(iOdds >> 16) // Odd height
		{
			if(iSects >> 16) // Start-angle > end-angle
			{
				var xl = (yEndA <= cy || yEndZ > cy)? (cx - x) : cx;
				this._mkDiv(xl, cy, x + cx - xl + (iOdds & 0xffff), 1);
			}
			else if((iSects & 0x01) && yEndZ > cy)
				this._mkDiv(cx - x, cy, x, 1);
		}
	};

/* fillPolygon method, implemented by Matthieu Haller.
This javascript function is an adaptation of the gdImageFilledPolygon for Walter Zorn lib.
C source of GD 1.8.4 found at http://www.boutell.com/gd/

THANKS to Kirsten Schulz for the polygon fixes!

The intersection finding technique of this code could be improved
by remembering the previous intertersection, and by using the slope.
That could help to adjust intersections to produce a nice
interior_extrema. */
	this.fillPolygon = function(array_x, array_y)
	{
		var i;
		var y;
		var miny, maxy;
		var x1, y1;
		var x2, y2;
		var ind1, ind2;
		var ints;

		var n = array_x.length;
		if(!n) return;

		miny = array_y[0];
		maxy = array_y[0];
		for(i = 1; i < n; i++)
		{
			if(array_y[i] < miny)
				miny = array_y[i];

			if(array_y[i] > maxy)
				maxy = array_y[i];
		}
		for(y = miny; y <= maxy; y++)
		{
			var polyInts = new Array();
			ints = 0;
			for(i = 0; i < n; i++)
			{
				if(!i)
				{
					ind1 = n-1;
					ind2 = 0;
				}
				else
				{
					ind1 = i-1;
					ind2 = i;
				}
				y1 = array_y[ind1];
				y2 = array_y[ind2];
				if(y1 < y2)
				{
					x1 = array_x[ind1];
					x2 = array_x[ind2];
				}
				else if(y1 > y2)
				{
					y2 = array_y[ind1];
					y1 = array_y[ind2];
					x2 = array_x[ind1];
					x1 = array_x[ind2];
				}
				else continue;

				 //  Modified 11. 2. 2004 Walter Zorn
				if((y >= y1) && (y < y2))
					polyInts[ints++] = Math.round((y-y1) * (x2-x1) / (y2-y1) + x1);

				else if((y == maxy) && (y > y1) && (y <= y2))
					polyInts[ints++] = Math.round((y-y1) * (x2-x1) / (y2-y1) + x1);
			}
			polyInts.sort(_CompInt);
			for(i = 0; i < ints; i+=2)
				this._mkDiv(polyInts[i], y, polyInts[i+1]-polyInts[i]+1, 1);
		}
	};

	this.drawString = function(txt, x, y)
	{
		this.htm += '<div style="position:absolute;white-space:nowrap;'+
			'left:' + x + 'px;'+
			'top:' + y + 'px;'+
			'font-family:' +  this.ftFam + ';'+
			'font-size:' + this.ftSz + ';'+
			'color:' + this.color + ';' + this.ftSty + '">'+
			txt +
			'<\/div>';
	};

/* drawStringRect() added by Rick Blommers.
Allows to specify the size of the text rectangle and to align the
text both horizontally (e.g. right) and vertically within that rectangle */
	this.drawStringRect = function(txt, x, y, width, halign)
	{
		this.htm += '<div style="position:absolute;overflow:hidden;'+
			'left:' + x + 'px;'+
			'top:' + y + 'px;'+
			'width:'+width +'px;'+
			'text-align:'+halign+';'+
			'font-family:' +  this.ftFam + ';'+
			'font-size:' + this.ftSz + ';'+
			'color:' + this.color + ';' + this.ftSty + '">'+
			txt +
			'<\/div>';
	};

	this.drawImage = function(imgSrc, x, y, w, h, a)
	{
		this.htm += '<div style="position:absolute;'+
			'left:' + x + 'px;'+
			'top:' + y + 'px;'+
			// w (width) and h (height) arguments are now optional.
			// Added by Mahmut Keygubatli, 14.1.2008
			(w? ('width:' +  w + 'px;') : '') +
			(h? ('height:' + h + 'px;'):'')+'">'+
			'<img src="' + imgSrc +'"'+ (w ? (' width="' + w + '"'):'')+ (h ? (' height="' + h + '"'):'') + (a? (' '+a) : '') + '>'+
			'<\/div>';
	};

	this.clear = function()
	{
		this.htm = "";
		if(this.cnv) this.cnv.innerHTML = "";
	};

	this._mkOvQds = function(cx, cy, x, y, w, h, wod, hod)
	{
		var xl = cx - x, xr = cx + x + wod - w, yt = cy - y, yb = cy + y + hod - h;
		if(xr > xl+w)
		{
			this._mkDiv(xr, yt, w, h);
			this._mkDiv(xr, yb, w, h);
		}
		else
			w = xr - xl + w;
		this._mkDiv(xl, yt, w, h);
		this._mkDiv(xl, yb, w, h);
	};
	
	this._mkArcDiv = function(x, y, oy, cx, cy, iOdds, aBndA, aBndZ, iSects)
	{
		var xrDef = cx + x + (iOdds & 0xffff), y2, h = oy - y, xl, xr, w;

		if(!h) h = 1;
		x = cx - x;

		if(iSects & 0xff0000) // Start-angle > end-angle
		{
			y2 = cy - y - h;
			if(iSects & 0x00ff)
			{
				if(iSects & 0x02)
				{
					xl = Math.max(x, aBndZ[y]);
					w = xrDef - xl;
					if(w > 0) this._mkDiv(xl, y2, w, h);
				}
				if(iSects & 0x01)
				{
					xr = Math.min(xrDef, aBndA[y]);
					w = xr - x;
					if(w > 0) this._mkDiv(x, y2, w, h);
				}
			}
			else
				this._mkDiv(x, y2, xrDef - x, h);
			y2 = cy + y + (iOdds >> 16);
			if(iSects & 0xff00)
			{
				if(iSects & 0x0100)
				{
					xl = Math.max(x, aBndA[y]);
					w = xrDef - xl;
					if(w > 0) this._mkDiv(xl, y2, w, h);
				}
				if(iSects & 0x0200)
				{
					xr = Math.min(xrDef, aBndZ[y]);
					w = xr - x;
					if(w > 0) this._mkDiv(x, y2, w, h);
				}
			}
			else
				this._mkDiv(x, y2, xrDef - x, h);
		}
		else
		{
			if(iSects & 0x00ff)
			{
				if(iSects & 0x02)
					xl = Math.max(x, aBndZ[y]);
				else
					xl = x;
				if(iSects & 0x01)
					xr = Math.min(xrDef, aBndA[y]);
				else
					xr = xrDef;
				y2 = cy - y - h;
				w = xr - xl;
				if(w > 0) this._mkDiv(xl, y2, w, h);
			}
			if(iSects & 0xff00)
			{
				if(iSects & 0x0100)
					xl = Math.max(x, aBndA[y]);
				else
					xl = x;
				if(iSects & 0x0200)
					xr = Math.min(xrDef, aBndZ[y]);
				else
					xr = xrDef;
				y2 = cy + y + (iOdds >> 16);
				w = xr - xl;
				if(w > 0) this._mkDiv(xl, y2, w, h);
			}
		}
	};

	this.setStroke(1);
	this.setFont("verdana,geneva,helvetica,sans-serif", "12px", Font.PLAIN);
	this.color = "#000000";
	this.htm = "";
	this.wnd = wnd || window;

	if(!jg_ok) _chkDHTM(this.wnd);
	if(jg_ok)
	{
		if(cnv)
		{
			if(typeof(cnv) == "string")
				this.cont = document.all? (this.wnd.document.all[cnv] || null)
					: document.getElementById? (this.wnd.document.getElementById(cnv) || null)
					: null;
			else if(cnv == window.document)
				this.cont = document.getElementsByTagName("body")[0];
			// If cnv is a direct reference to a canvas DOM node
			// (option suggested by Andreas Luleich)
			else this.cont = cnv;
			// Create new canvas inside container DIV. Thus the drawing and clearing
			// methods won't interfere with the container's inner html.
			// Solution suggested by Vladimir.
			this.cnv = this.wnd.document.createElement("div");
			this.cnv.style.fontSize=0;
			this.cont.appendChild(this.cnv);
			this.paint = jg_dom? _pntCnvDom : _pntCnvIe;
		}
		else
			this.paint = _pntDoc;
	}
	else
		this.paint = _pntN;

	this.setPrintable(false);
}

function _mkLinVirt(aLin, x1, y1, x2, y2)
{
	var dx = Math.abs(x2-x1), dy = Math.abs(y2-y1),
	x = x1, y = y1,
	xIncr = (x1 > x2)? -1 : 1,
	yIncr = (y1 > y2)? -1 : 1,
	p,
	i = 0;
	if(dx >= dy)
	{
		var pr = dy<<1,
		pru = pr - (dx<<1);
		p = pr-dx;
		while(dx > 0)
		{--dx;
			if(p > 0)    //  Increment y
			{
				aLin[i++] = x;
				y += yIncr;
				p += pru;
			}
			else p += pr;
			x += xIncr;
		}
	}
	else
	{
		var pr = dx<<1,
		pru = pr - (dy<<1);
		p = pr-dy;
		while(dy > 0)
		{--dy;
			y += yIncr;
			aLin[i++] = x;
			if(p > 0)    //  Increment x
			{
				x += xIncr;
				p += pru;
			}
			else p += pr;
		}
	}
	for(var len = aLin.length, i = len-i; i;)
		aLin[len-(i--)] = x;
};

function _CompInt(x, y)
{
	return(x - y);
}






function roundPrecision(theValue,thePrecision)
{ return(Math.round(theValue/thePrecision)*thePrecision);
}

// function canvasOnClick(canvas)
// function canvasOnMouseMove(canvas)

// function coordHrDecToHrMin(theHr)
// function coordDegDecToDegMin(theDeg)


// function jgCanvasDrawLine(X1,Y1,X2,Y2);										  								// Draws a line in absolute coordinates relative to the canvas;
// function jgCanvasDrawPoint(X,Y);																							// Draws a point in absolute coordinates relative to the canvas;
// function jgCanvasDrawString(theString,Apx,Bpx);
// function jgCanvasInit(canvas,originX,originY,widthX,heightY,BkGroundColor);  // Initialises the canvas;
// function jgGridClearBorder();																								// Clears the grid border;
// function jgGridDraw();																												// draws the grid;
// function jgGridDrawLine(X1,Y1,X2,Y2);																				// draws a line in the grid coordinates
// function jgGridInit(goX,goY,gX0,gY0,gX1,gY1);																// Initialises the grid offsets in X and Y, and the grid coordinate limits (X0,Y0) (X1,Y1);
// function jgGridLDRad(L,D);																										// Calculate (ra,decl) to (X,Y)
// function jgGridSetBorderColor(theColor);																			// Sets the grid border color;
// function jgGridSetLD(theL0,theD0,theL1,theD1);                               // Sets the grid ra and decl coordinates;
// function jgPaint();																													// Draws the canvas and contents;
// function jgSetDrawColor(theColor);																						// Sets the drawing color, new objects will be drawn in this color;

var cnv;
var jg;	

var hotZones = new Array();
var astroObjects = new Array();
var astroObjectsHotZones = new Array();

var onClickHandling=false;	                                                                      // Java Graphics object;

// Positioning Parameters
var canvasDimensionXpx,  canvasDimensionYpx;                                    // Canvas Dimension X and Dimension Y: width and height of the canvas;
var gridBorder=true;     gridCoordLines=true;						            // Grid border (true or false)
var gridCenterOffsetXpx, gridCenterOffsetYpx;                                   // Grid center offset X and offset Y: distance obetween the grid and the canvas border;
var gridL0rad,           gridD0rad;		                                        // Grid center coordinates ra and decl
var gridWidthXpx,        gridHeightYpx;                                         // Grid dimensions in X and Y;
var gridSpanLrad,        gridSpanDrad;  			                            // Grid span in L and D in rad
var gridXpx,             gridYpx;

var ra=0; decl=0;

var divOffsetXpx =20;
var divOffsetYpx =20;
var divOffsetX2px=20;
var divOffsetY2px=20;
var canvasOffsetXpx=20;
var canvasOffsetYpx=20;
var gridOffsetXpx=100;
var gridOffsetYpx=50;

// Color Parameters
var buttonBkColor          ='#AAAAAA';
var buttonTextColor        ='#FFFF00';
var canvasBkGroundColor    ='#000000';                                          // Background color of the canvas;
var coordLineColor         ='#EE0000';                                          // Coordinate grid line colors
var coordLblBkGroundColor  ='#000000';                                          // Background Color of coordinate Labels
var coordLblColor          ='#DDDDDD';                                          // Color of coordinate labels
var coordBkGroundColor     ='#000000';                                          // Background color of coordinates of mouse position
var coordColor             ='#AAAAAA';                                          // Color of coordinates of mouse position
var gridBorderColor        ='#FFFF00';                                          // Color of the grid border
var starColor              ='#FFFF00';
var gridHeightYpx2         =0;

// Layout Parameters
var coordGridsH, coordGridsV;                                                   // Obsolete - Number of grid lines H and V
var coordCnvXpx, coorCnvYpx;                                                    // Location of mouse coordinate positions
var Lsteps=10,   Dsteps=10;                                                     // Number of steps for drawing coordinate lines between major steps
var gridDimensions=new Array(
  new Array(180,80.00,6.000),
  new Array(150,60.00,4.000),
  new Array(120,50.00,3.000),
	new Array( 90,40.00,2.666),
	new Array( 75,30.00,2.000),
	new Array( 60,24.50,1.666),
	new Array( 45,20.00,1.333),
	new Array( 35,15.00,1.000),
	new Array( 30,12.00,0.800),
	new Array( 25,10.00,0.666),
	new Array( 20, 8.00,0.633),
	new Array( 15, 6.00,0.400),
	new Array( 10, 4.00,0.266),
	new Array(  7, 3.00,0.200),
	new Array(  5, 2.00,0.133),
	new Array(  4, 1.50,0.100),
	new Array(  3, 1.00,0.066),
	new Array(  2, 0.80,0.050),
	new Array(  1, 0.40,0.026),
	new Array(0.5, 0.20,0.012),
	new Array(0.25,0.10,0.006)
	);
var gridActualDimension=14;
var gridMaxDimension=20;
var gridMinDimension=0;

// Help parameters for parameter passing
var dsl_hr,     dsl_min,         dsl_sec;                                       // fn coordHrDecToHrMin    results
var dsl_deg,    dsl_amn,         dsl_asc;                                       // fn coordDeclDecToDegMin results
var canvasX1px, canvasY1px,      canvasX2px,     canvasY2px;                    // fn jgGridDrawLineLD     points from - to where the line is drawn (= intersection with the grid)
var gridLx1rad, gridDy1rad,      gridLx2rad,     gridDy2rad;                    // fn jgGridDrawLineLD     points from - to where the line is drawn (= intersection with the grid)
var gridLxRad, gridDyRad;

// Canvas actions
function canvasCursor(theCursor)
{ cnv.style.cursor = theCursor;
}
function canvasOnClick(event)
{ if(onClickHandling==true)
    return;
	onClickHandling=true;
	//alert('X:'+event.clientX);
  x=event.clientX-canvasOffsetXpx-cnv.offsetLeft-2;
  y=canvasDimensionYpx-event.clientY+cnv.offsetTop+canvasOffsetYpx+2;
	for(i=0;i<hotZones.length;i++)
	{ //alert(x+' '+hotZones[i][0]+' '+(hotZones[i][0]+hotZones[i][2])+' '+y+' '+hotZones[i][1]+' '+(hotZones[i][1]+hotZones[i][3]));
    if((hotZones[i][0]<=x)&&(x<=(hotZones[i][0]+hotZones[i][2]))&&(hotZones[i][1]<=y)&&(y<=(hotZones[i][1]+hotZones[i][3])))
		{ switch(hotZones[i][4])
			{ case 'actionZoomIn':
			    jgGridZoomIn(hotZones[i][5]);break;
        case 'actionGridOff':
			    gridCoordLines=false;jgGridRedraw();break;
        case 'actionGridOn':
			    gridCoordLines=true; jgGridRedraw();break;
        case 'actionGridLeft':
			    jgGridMove('Left');  jgGridRedraw();break;
        case 'actionGridRight':
			    jgGridMove('Right'); jgGridRedraw();break;
        case 'actionGridUp':
			    jgGridMove('Up');    jgGridRedraw();break;
        case 'actionGridDown':
			    jgGridMove('Down');  jgGridRedraw();break;
			}
		}
	}
	jgPaint();
  onClickHandling=false;
}
function canvasOnMouseMove(event)
{ x=event.clientX-cnv.offsetLeft-2;
  y=event.clientY-cnv.offsetTop-2;
	if((x>canvasOffsetXpx+gridOffsetXpx)&&(x<canvasOffsetXpx+gridOffsetXpx+gridWidthXpx)&&(y>canvasOffsetYpx+gridOffsetYpx)&&(y<canvasOffsetYpx+gridOffsetYpx+gridHeightYpx))
	{	jgGridLDinvRad(x,y);
	  jgCanvasDrawLabel(coordBkGroundColor,coordColor,coordGridLxDyToString(),2,2,150,15,'center')
	}
  jgPaint();
}
// Presentation functions
function coordHrDecToHrMin(theHr)
{ while((theHr)>24) theHr-=24;
  while((theHr)<0)  theHr+=24;
  dsl_hr=Math.floor(theHr);
  dsl_min=Math.round((theHr-dsl_hr)*60);
	if(dsl_min==60)
	{ dsl_min=0;
	  ++dsl_hr;
		if(dsl_hr==24)
		  dsl_hr=0;
	}
	if(dsl_min>0)
	  return dsl_hr+'h'+dsl_min+'m';
	return dsl_hr+'h';
}
function coordHrDecToHrMinSec(theHr)
{ while((theHr)>24) theHr-=24;
  while((theHr)<0)  theHr+=24;
  dsl_hr=Math.floor(theHr);
  dsl_min=Math.floor((theHr-dsl_hr)*60);
	dsl_sec=roundPrecision((theHr-dsl_hr-(dsl_min/60))*3600,10);
	if(dsl_sec==60)
	{ ++dsl_min;
		dsl_sec=0;
	}
	if(dsl_min==60)
	{ dsl_min=0;
	  ++dsl_hr;
	}
  if(dsl_hr==24)
    dsl_hr=0;
	if(dsl_sec>0)
	  return dsl_hr+'h'+dsl_min+'m'+dsl_sec+'s';
	else if(dsl_min>0)
	  return dsl_hr+'h'+dsl_min+'m';
	return dsl_hr+'h';
}
function coordDeclDecToDegMin(theDeg)
{ if(theDeg>90)  theDeg=90;
  if(theDeg<-90) theDeg=-90;
	sign='';if(theDeg<0) {theDeg=-theDeg; sign='-';}
  dsl_deg=Math.floor(theDeg);
  dsl_amn=Math.round((theDeg-dsl_deg)*60);
	if(dsl_amn==60)
	{ dsl_amn=0;
	  ++dsl_deg;
	}
	dsl_deg=sign+dsl_deg;
	if(dsl_amn>0)
  	return dsl_deg+'°'+dsl_amn+'\'';
  return dsl_deg+'°';
}
function coordGridLxDyToString()
{ coordHrDecToHrMinSec(gridLxRad/Math.PI*12);
	coordDeclDecToDegMin(gridDyRad/Math.PI*180);
  return '('+dsl_hr+'h'+dsl_min+'m'+dsl_sec+'s, '+dsl_deg+'°'+dsl_amn+'\')';
}
function initDiv(theDiv)
{ document.getElementById(theDiv).style.left  =divOffsetXpx+'px';
  document.getElementById(theDiv).style.top   =divOffsetYpx+'px';
  document.getElementById(theDiv).style.width =(div5Width-divOffsetXpx-divOffsetX2px)+'px';
  document.getElementById(theDiv).style.height=(div5Height-divOffsetYpx-divOffsetY2px)+'px';
}
// Canvas graphics functions
function jgCanvasDrawButton(x,y,w,h,theText)
{ jgSetDrawColor(buttonBkColor);
  jgCanvasFillRect(x,y,w,h);
	jgCanvasDrawLabel(buttonBkColor,buttonTextColor,theText,x+2,y+2,w-4,h-4,'center');
}
function jgCanvasDrawEllipseTilt(cx,cy,w,h,angle)
{	angle=-angle;
  jg.drawEllipseTiltLimited(canvasOffsetXpx+cx,canvasOffsetYpx+canvasDimensionYpx-cy,w,h,angle,canvasOffsetXpx+gridOffsetXpx,canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,canvasOffsetYpx+gridOffsetYpx+gridHeightYpx,canvasOffsetYpx+gridOffsetYpx);
}
function jgCanvasDrawFilledCircle(cx,cy,d)
{	return jg.fillEllipse(Math.round(canvasOffsetXpx+cx-(d/2)),Math.round(canvasOffsetYpx+canvasDimensionYpx-cy-(d/2)),d,d);
}
function jgCanvasDrawLabel(bkGrdColor,textColor,theLabel,a,b,w,h,align)
{ jgSetDrawColor(bkGrdColor);
	jgCanvasFillRect(a,b,w,h);
	jgSetDrawColor(textColor);
	jgCanvasDrawStringRect(theLabel,a,b,w,h,align);
}
function jgCanvasDrawLine(A1px,B1px,A2px,B2px)
{ return jg.drawLine(canvasOffsetXpx+A1px,canvasOffsetYpx+canvasDimensionYpx-B1px,canvasOffsetXpx+A2px,canvasOffsetYpx+canvasDimensionYpx-B2px);
}
function jgCanvasDrawPoint(Apx,Bpx)
{ return jg.drawLine(canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx);
}
function jgCanvasDrawString(theString,Apx,Bpx)
{ return jg.drawString(theString,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx);
}
function jgCanvasDrawStringRect(theString,Apx,Bpx,w,h,theAlignment)
{ return jg.drawStringRect(theString,canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx-h,w,theAlignment);
}
function jgCanvasFillRect(Apx,Bpx,Widthpx,Heightpx)
{ return jg.fillRect(canvasOffsetXpx+Apx,canvasOffsetYpx+canvasDimensionYpx-Bpx-Heightpx,Widthpx,Heightpx);
}
function jgCanvasInit(canvas)
{ canvasDimensionXpx=((div5Width-divOffsetXpx-divOffsetX2px)-(2*canvasOffsetXpx));
  canvasDimensionYpx=((div5Height-divOffsetYpx-divOffsetY2px)-(2*canvasOffsetYpx));
  cnv=document.getElementById(canvas);
  if(!cnv) return false;
	return jg=new jsGraphics(cnv);
}
function jgCanvasRedraw()
{ jg.setColor(canvasBkGroundColor);
	jg.fillRect(canvasOffsetXpx,canvasOffsetYpx,canvasDimensionXpx,canvasDimensionYpx);
  jg.drawRect(canvasOffsetXpx,canvasOffsetYpx,canvasDimensionXpx,canvasDimensionYpx);
	return jg;
}
// Grid graphics functions
function jgGridClearBorder()
{ gridBorder=false;
}
function jgGridDrawBorder()
{ if(gridBorder)
  { jgSetDrawColor(gridBorderColor);
	  jgCanvasDrawLine(gridOffsetXpx,gridOffsetYpx,canvasDimensionXpx-gridOffsetXpx,gridOffsetYpx);
	  jgCanvasDrawLine(canvasDimensionXpx-gridOffsetXpx,gridOffsetYpx,canvasDimensionXpx-gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx);
	  jgCanvasDrawLine(canvasDimensionXpx-gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx,gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx);
	  jgCanvasDrawLine(gridOffsetXpx,canvasDimensionYpx-gridOffsetYpx,gridOffsetXpx,gridOffsetYpx);
	}
}
function jgGridDrawCoordLines()
{ jgGridLDinvRad(canvasOffsetXpx+gridOffsetXpx,canvasOffsetYpx+gridOffsetYpx);
  luLrad=gridLxRad;
  luDrad=gridDyRad;
  jgGridLDinvRad(canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,canvasOffsetYpx+gridOffsetYpx);
  ruLrad=gridLxRad;
  ruDrad=gridDyRad;
  jgGridLDinvRad(canvasOffsetXpx+gridOffsetXpx,canvasOffsetYpx+gridOffsetYpx+gridHeightYpx);
  ldLrad=gridLxRad;
  ldDrad=gridDyRad;
  jgGridLDinvRad(canvasOffsetXpx+gridOffsetXpx+gridWidthXpx,canvasOffsetYpx+gridOffsetYpx+gridHeightYpx);
  rdLrad=gridLxRad;
  rdDrad=gridDyRad;
  if(((gridD0rad+gridSpanDrad)<(Math.PI/2))&&((gridD0rad-gridSpanDrad)>-(Math.PI/2)))
	{	if(gridD0rad>0)
	  { Lrad=luLrad;
	    Rrad=ruLrad;
    }
		else
	  { Lrad=ldLrad;
	    Rrad=rdLrad;
    }
		if(Lrad<Rrad)
		  Rrad-=(Math.PI*2);
		Urad=Math.max(gridD0rad+gridSpanDrad,Math.max(luDrad,ruDrad));
	  Drad=Math.min(gridD0rad-gridSpanDrad,Math.min(ldDrad,rdDrad));
		Lhr=Lrad/Math.PI*12;
		RhrNeg=Rrad/Math.PI*12;
		Udeg=Urad/Math.PI*180;
		Ddeg=Drad/Math.PI*180;
		coordGridsH=Math.max(coordGridsH,1);
		coordGridsV=Math.max(coordGridsV,1);
	}
  else if((gridD0rad+gridSpanDrad)>=(Math.PI/2))
	{ Lhr=gridL0rad/Math.PI*12+12+.01;
	  RhrNeg=gridL0rad/Math.PI*12-12;
		Udeg=90;
		Ddeg=Math.min(gridD0rad-gridSpanDrad,Math.min(ldDrad,rdDrad))/Math.PI*180;
	}
  else if((gridD0rad-gridSpanDrad)<=-(Math.PI/2))
	{ Lhr=gridL0rad/Math.PI*12+12+.01;
	  RhrNeg=gridL0rad/Math.PI*12-12;
		Udeg=Math.max(gridD0rad+gridSpanDrad,Math.max(luDrad,ruDrad))/Math.PI*180;
		Ddeg=-90;
	}
	
	DLhr=(Lhr-RhrNeg);
	LStep=gridDimensions[gridActualDimension][2];
	DDdeg=(Udeg-Ddeg);
	DStep=gridDimensions[gridActualDimension][1];
	
	LhrStart=(Math.floor(Lhr/LStep)+1)*LStep;
 	DdegStart=(Math.floor(Ddeg/DStep)+1)*DStep;

  for(d=DdegStart;d<=Udeg;d+=DStep)
	{ canvasX2px=0;
	  jgSetDrawColor(coordLineColor);
    for(l=Lhr;l>RhrNeg;l-=LStep/Lsteps)
      jgGridDrawLineLD(l,d,(l-(LStep/Lsteps)),d);
    if(canvasX2px&&(canvasX2px>=gridOffsetXpx+gridWidthXpx))
      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),gridOffsetXpx+gridWidthXpx+2,canvasY2px-8,60,15,'left');
		else if(canvasX2px&&(canvasY2px>=gridOffsetYpx+gridHeightYpx))
	    jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),canvasX2px-30,gridOffsetYpx+gridHeightYpx+2,60,15,'center');
		else if(canvasX2px&&(canvasY2px<=gridOffsetYpx))
      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),canvasX2px-30,gridOffsetYpx-8,60,15,'center');
    else if(canvasX2px)
	    jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordDeclDecToDegMin(d),canvasX2px-30,canvasY2px-17,60,15,'center');
	}
  if(gridD0rad<0)
	{ for(l=LhrStart;l>RhrNeg;l-=LStep)
    {	canvasX2px=0;
	    jgSetDrawColor(coordLineColor);
      for(d=Ddeg;d<Udeg;d+=DStep/Dsteps)
	      jgGridDrawLineLD(l,d,l,(d+(DStep/Dsteps)));
      if(canvasX2px&&(canvasY2px<=gridOffsetYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx-17,60,15,'center');
			else if(canvasX2px&&(canvasX2px<=gridOffsetXpx)&&(canvasY2px<gridOffsetYpx+gridHeightYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),gridOffsetXpx-62,canvasY2px-8,60,15,'right');
			else if(canvasX2px&&(canvasX2px>=gridOffsetXpx+gridWidthXpx)&&(canvasY2px<gridOffsetYpx+gridHeightYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),gridOffsetXpx+gridWidthXpx+2,canvasY2px-8,60,15,'left');
			else if(canvasX2px&&(canvasY2px>=gridOffsetYpx+gridHeightYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx+gridHeightYpx+2,60,15,'center');
	  }
  }
	else
	{ for(l=LhrStart;l>RhrNeg;l-=LStep)
    {	canvasX2px=0;
	    jgSetDrawColor(coordLineColor);
      for(d=Udeg;d>Ddeg;d-=DStep/Dsteps)
	      jgGridDrawLineLD(l,d,l,(d-(DStep/Dsteps)));
      if(canvasX2px&&(canvasY2px<=gridOffsetYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx-17,60,15,'center');
			else if(canvasX2px&&(canvasX2px<=gridOffsetXpx)&&(canvasY2px<gridOffsetYpx+gridHeightYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),gridOffsetXpx-62,canvasY2px-8,60,15,'right');
			else if(canvasX2px&&(canvasX2px>=gridOffsetXpx+gridWidthXpx)&&(canvasY2px<gridOffsetYpx+gridHeightYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),gridOffsetXpx+gridWidthXpx+2,canvasY2px-8,60,15,'left');
			else if(canvasX2px&&(canvasY2px>=gridOffsetYpx+gridHeightYpx))
	      jgCanvasDrawLabel(coordLblBkGroundColor,coordLblColor,coordHrDecToHrMin(l),canvasX2px-30,gridOffsetYpx+gridHeightYpx+2,60,15,'center');
	  }
	}
}
function jgGridDrawEllipseTilt(Lhr,Ddeg,Diam1Sec,Diam2Sec,AngleDeg)
{ AngleDeg=(AngleDeg+90)%180;
  jgGridLDrad(Lhr,Ddeg); 
	x1=gridLxRad; y1=gridDyRad;
	jgCanvasDrawEllipseTilt(gridCenterOffsetXpx+jgGridXpx(x1),gridCenterOffsetYpx+jgGridYpx(y1),Math.round((gridHeightYpx2*(Diam1Sec/3600/12*Math.PI)/gridSpanDrad)),Math.round((gridHeightYpx2*(Diam2Sec/3600/12*Math.PI)/gridSpanDrad)),(AngleDeg/180*Math.PI));
}
function jgGridDrawFilledCirclePx(Lhr,Ddeg,DiamPx)
{ jgGridLDrad(Lhr,Ddeg); 
	cx=gridCenterOffsetXpx+jgGridXpx(gridLxRad);
	cy=gridCenterOffsetYpx+jgGridYpx(gridDyRad);
  if((cx-DiamPx<gridOffsetXpx)||(cx+DiamPx>gridOffsetXpx+gridWidthXpx)) return false;
	if((cy+DiamPx>gridOffsetYpx+gridHeightYpx)||(cy-DiamPx<gridOffsetYpx)) return false;
  jgCanvasDrawFilledCircle(cx,cy,DiamPx,DiamPx);
	return true;
}
function jgGridDrawLinePxLR(Lhr,Ddeg,d)
{ jgGridLDrad(Lhr,Ddeg); 
	cx=gridCenterOffsetXpx+jgGridXpx(gridLxRad);
	cy=gridCenterOffsetYpx+jgGridYpx(gridDyRad);
  if((cx-d<gridOffsetXpx)||(cx+d>gridOffsetXpx+gridWidthXpx)) return;
	if((cy>gridOffsetYpx+gridHeightYpx)||(cy<gridOffsetYpx)) return;
  jgCanvasDrawLine(cx-d,cy,cx+d,cy);
}
function jgGridDrawLineLD(Lhr1,Ddeg1,Lhr2,Ddeg2)
{ jgGridLDrad(Lhr1,Ddeg1); x1=gridLxRad; y1=gridDyRad;
	jgGridLDrad(Lhr2,Ddeg2); x2=gridLxRad; y2=gridDyRad;
	
	var intersect=true;
	if((x1<-gridSpanLrad)&&(x2<-gridSpanLrad)) return;
	if((x1>gridSpanLrad)&&(x2>gridSpanLrad))   return;
	if((y1<-gridSpanDrad)&&(y2<-gridSpanDrad)) return;
	if((y1>gridSpanDrad)&&(y2>gridSpanDrad))   return;
	if(intersect&&(x1<-gridSpanLrad)) if(x2==x1) return; else {y1=(((-gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1; x1=-gridSpanLrad;}
	if(intersect&&(x1>gridSpanLrad))  if(x2==x1) return; else	{y1=(((gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1;  x1=gridSpanLrad; }
	if(intersect&&(y1>gridSpanDrad))  if(y2==y1) return; else	{x1=(((gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1;  y1=gridSpanDrad; }
	if(intersect&&(y1<-gridSpanDrad)) if(y2==y1) return; else {x1=(((-gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1; y1=-gridSpanDrad;}
	if((y1<-gridSpanDrad)||(y1>gridSpanDrad)||(x1<-gridSpanLrad)||(x1>gridSpanLrad)) return;	
	if(intersect&&(x2<-gridSpanLrad)) if(x2==x1) return; else {y2=(((-gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1; x2=-gridSpanLrad;}
	if(intersect&&(x2>gridSpanLrad))  if(x2==x1) return; else	{y2=(((gridSpanLrad-x1)/(x2-x1))*(y2-y1))+y1;  x2=gridSpanLrad;	}
  if(intersect&&(y2>gridSpanDrad))  if(y2==y1) return; else	{x2=(((gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1;  y2=gridSpanDrad;	}
	if(intersect&&(y2<-gridSpanDrad)) if(y2==y1) return; else	{x2=(((-gridSpanDrad-y1)/(y2-y1))*(x2-x1))+x1; y2=-gridSpanDrad;}
  if((y2<-gridSpanDrad)||(y2>gridSpanDrad)||(x2<-gridSpanLrad)||(x2>gridSpanLrad)) return;
	
	canvasX1px=gridCenterOffsetXpx+jgGridXpx(x1);
  canvasY1px=gridCenterOffsetYpx+jgGridYpx(y1);
  canvasX2px=gridCenterOffsetXpx+jgGridXpx(x2);
  canvasY2px=gridCenterOffsetYpx+jgGridYpx(y2);
	gridLx1rad=x1;gridDy1rad=y1;gridLx2rad=x2;gridDy2rad=y2;
	jgCanvasDrawLine(canvasX1px,canvasY1px,canvasX2px,canvasY2px);
}
function jgGridDrawPointLD(Lhr,Ddeg)
{ jgGridLDrad(Lhr,Ddeg);
  if((gridLxRad>-gridSpanLrad)&&(gridLxRad<gridSpanLrad)&&(gridDyRad>-gridSpanDrad)&&(gridDyRad<gridSpanDrad))
	  return jgCanvasDrawPoint(gridCenterOffsetXpx+jgGridXpx(gridLxRad),gridCenterOffsetYpx+jgGridYpx(gridDyRad));
  else return 0;
}
function jgGridInit()
{	gridWidthXpx=canvasDimensionXpx-gridOffsetXpx-gridOffsetXpx;
	gridWidthXpx2=Math.round(gridWidthXpx/2);
	gridCenterOffsetXpx=gridOffsetXpx+Math.round(gridWidthXpx/2);
	gridHeightYpx=canvasDimensionYpx-gridOffsetYpx-gridOffsetYpx;
	gridHeightYpx2=Math.round(gridHeightYpx/2);
	gridCenterOffsetYpx=gridOffsetYpx+Math.round(gridHeightYpx/2);
}
function jgGridInitScale(gridLHr,gridDdeg,desiredScale)
{ gridActualDimension=gridMaxDimension;
	while((gridActualDimension>gridMinDimension)&&(gridDimensions[gridActualDimension][0]<desiredScale))
	  gridActualDimension--;
	gridL0rad=gridLHr/12*Math.PI;
	gridD0rad=gridDdeg/180*Math.PI;
  if(gridWidthXpx<gridHeightYpx)
  {	gridSpanD=gridDimensions[gridActualDimension][0]*(gridHeightYpx/gridWidthXpx);
		gridSpanL=gridDimensions[gridActualDimension][0];
	}
	else
	{ gridSpanD=gridDimensions[gridActualDimension][0];
		gridSpanL=gridDimensions[gridActualDimension][0]*(gridWidthXpx/gridHeightYpx);
	}
	gridSpanLrad=gridSpanL/180*Math.PI;
	gridSpanDrad=gridSpanD/180*Math.PI;
}
function jgGridInitScaleFixed(gridLHr,gridDdeg,desiredScale)
{ gridActualDimension=desiredScale;
  gridL0rad=gridLHr/12*Math.PI;
	gridD0rad=gridDdeg/180*Math.PI;
  if(gridWidthXpx<gridHeightYpx)
  {	gridSpanD=gridDimensions[gridActualDimension][0]*(gridHeightYpx/gridWidthXpx);
		gridSpanL=gridDimensions[gridActualDimension][0];
	}
	else
	{ gridSpanD=gridDimensions[gridActualDimension][0];
		gridSpanL=gridDimensions[gridActualDimension][0]*(gridWidthXpx/gridHeightYpx);
	}
	gridSpanLrad=gridSpanL/180*Math.PI;
	gridSpanDrad=gridSpanD/180*Math.PI;
}
function jgGridLDrad(Lhr,Ddeg)
{ Lrad=Lhr/12*Math.PI; Drad=Ddeg/180*Math.PI;
	if(Lrad>gridL0rad+Math.PI) Lrad=Lrad-(Math.PI*2);
	if(Lrad<gridL0rad-Math.PI) Lrad=Lrad+(Math.PI*2);
	var drad=Math.acos((Math.sin(gridD0rad)*Math.sin(Drad))+(Math.cos(gridD0rad)*Math.cos(Drad)*Math.cos(Lrad-gridL0rad)));
	if(drad>0)
	{  gridLxRad=-(drad*(Math.sin(Lrad-gridL0rad)*Math.cos(Drad)/Math.sin(drad)));
	   gridDyRad=(drad*(Math.sin(Drad)-(Math.sin(gridD0rad)*Math.cos(drad)))/(Math.cos(gridD0rad)*Math.sin(drad)));
	}
	else
	{ gridLxRad=0;
	  gridDyRad=0;
	}
}
function jgGridLDinvRad(Xpx,Ypx)
{ xRad=-((Xpx-gridCenterOffsetXpx-canvasOffsetXpx)/gridWidthXpx2*gridSpanLrad);
  yRad=((gridCenterOffsetYpx+canvasOffsetYpx-Ypx)/gridHeightYpx2*gridSpanDrad);
  drad=Math.sqrt((xRad*xRad)+(yRad*yRad));
  if(drad>0)
	{ var sinalpha=xRad/drad;
	  var cosalpha=yRad/drad;
		var Dacc=Math.acos((Math.cos(drad)*Math.sin(gridD0rad))+(Math.sin(drad)*Math.cos(gridD0rad)*cosalpha));
		var cosLacc=(Math.cos(drad)-(Math.sin(gridD0rad)*Math.cos(Dacc)))/(Math.cos(gridD0rad)*Math.sin(Dacc));
		if(cosLacc>=0)
  		gridLxRad=gridL0rad+(Math.asin(Math.sin(drad)*sinalpha/Math.sin(Dacc)));
		else
  		gridLxRad=gridL0rad+Math.PI-(Math.asin(Math.sin(drad)*sinalpha/Math.sin(Dacc)));		
		gridDyRad=((Math.PI/2)-Dacc);
	}
	else
	{ gridLxRad=gridL0rad;
	  gridDyRad=gridD0rad;
	}
	if((gridDyRad)>(Math.PI/2))
	  gridDyRad=(Math.PI/2);
	if((gridDyRad)<(-Math.PI/2))
	  gridDyRad=(-Math.PI/2);
	if((gridLxRad)<0)
	  gridLxRad=gridLxRad+(2*Math.PI);
	if((gridLxRad)>=(2*Math.PI))
	  gridLxRad=gridLxRad-(2*Math.PI);
}
function jgGridMove(Direction)
{ if(Direction=='Left')
  { gridL0rad+=gridSpanLrad;
	  if(gridL0rad>=2*Math.PI)
		  gridL0rad-=2*Math.PI;
  }
  if(Direction=='Right')
  { gridL0rad-=gridSpanLrad;
	  if(gridL0rad<0)
		  gridL0rad+=2*Math.PI;
	}
  if(Direction=='Up')
  { gridD0rad+=gridSpanDrad;
	  if(gridD0rad>=Math.PI)
		  gridD0rad=Math.PI;
	}
  if(Direction=='Down')
  { gridD0rad-=gridSpanDrad;
	  if(gridD0rad<-Math.PI)
		  gridD0rad=-Math.PI;
	}
}
function jgGridRedraw()
{ jgCanvasRedraw();
  jgGridSetBorderColor(gridBorderColor);
  if(gridCoordLines)
	  jgGridDrawCoordLines();
  jgDrawHotZones();
	jgDrawAstroObjects();
	jgGridDrawBorder();
}
function jgGridSetBorderColor(theColor)
{ gridBorder=true;
  gridBorderColor=theColor;
}
function jgGridXpx(Lrad) 
{ return Math.round((gridWidthXpx2*Lrad/gridSpanLrad));
}
function jgGridYpx(Drad)
{ return Math.round((gridHeightYpx2*Drad/gridSpanDrad));
}
function jgGridZoomIn(zoomFactor)
{ jgGridInitScaleFixed(gridL0rad/Math.PI*12,gridD0rad/Math.PI*180,Math.max(Math.min(gridActualDimension+zoomFactor,gridMaxDimension),gridMinDimension));
  jgGridRedraw();
}
function jgGridSetHotZones()
{ jgSetHotZone(2,  canvasDimensionYpx-23,94,20,'actionZoomIn',2,'Button','Zoom In');
  jgSetHotZone(102,canvasDimensionYpx-23,94,20,'actionZoomIn',-2,'Button','Zoom Out');
  jgSetHotZone(2,  canvasDimensionYpx-46,94,20,'actionGridOff',-2,'Button','Grid Off');
  jgSetHotZone(102,canvasDimensionYpx-46,94,20,'actionGridOn',-2,'Button','Grid on');
  jgSetHotZone(2,                                    Math.round(canvasDimensionYpx/2)-10, 20, 20, 'actionGridLeft', -2,'Button','L');
  jgSetHotZone(canvasDimensionXpx-22,                Math.round(canvasDimensionYpx/2)-10, 20, 20, 'actionGridRight',-2,'Button','R');
  jgSetHotZone(Math.round(canvasDimensionXpx/2)-10,  canvasDimensionYpx-23,               20, 20, 'actionGridUp',   -2,'Button','U');
  jgSetHotZone(Math.round(canvasDimensionXpx/2)-10,  2,                                   20, 20, 'actionGridDown', -2,'Button','D');
}

// jg functions
function jgDrawAstroObjects()
{ for(i=0;i<astroObjects.length;i++)
  { if(astroObjects[i][0]=='Star')
      astroDrawStar(astroObjects[i][1],astroObjects[i][2],astroObjects[i][6]);
    if(astroObjects[i][0]=='DStar')
      astroDrawDStar(astroObjects[i][1],astroObjects[i][2],astroObjects[i][6]);
	}
}
function jgPaint()
{ return jg.paint();
}
function jgSetDrawColor(theColor)
{ return jg.setColor(theColor);
}
function jgDrawHotZones()
{ for(i=0;i<hotZones.length;i++)
  { if(hotZones[i][6]=='Button')
	    jgCanvasDrawButton(hotZones[i][0],hotZones[i][1],hotZones[i][2],hotZones[i][3],hotZones[i][7]);
	}
}
function jgSetHotZone(x,y,w,h,action,actionPar,theType,theText)
{ hotZones[hotZones.length]=new Array(x,y,w,h,action,actionPar,theType,theText);
}
function jgSetAstro(objectType,x,y,diam1,diam2,pa,mag,sb,objectname,altnames,seen,lastseendate)
{ astroObjects[astroObjects.length]=new Array(objectType,x,y,diam1,diam2,pa,mag,sb,objectname,altnames,seen,lastseendate);
}

// Astro functions
function astroDrawStar(Lhr,Ddeg,mag)
{ d=Math.round((gridActualDimension-mag-1)*1.5);
	if(d<=0) return;
	jgSetDrawColor(starColor);
	jgGridDrawFilledCirclePx(Lhr,Ddeg,d);
}
function astroDrawDStar(Lhr,Ddeg,mag)
{ d=Math.round((gridActualDimension-mag-1)*1.5);
	e=Math.max(Math.round(d*1.2),d+2);
	if(d<=0) return false;
	jgSetDrawColor(starColor);
	if(jgGridDrawFilledCirclePx(Lhr,Ddeg,d))
  	jgGridDrawLinePxLR(Lhr,Ddeg,d);
	return true;
}
function jgGridSetAstroObjects()
{  // Demo data
  jgSetAstro('Star',0,    0.0,0,0,0,8, 0,'Alcor','B UMa', 'X(5)',   '');
	jgSetAstro('Star',0.12, 0.1,0,0,0,10,0,'Mizar','24 UMa','X(4)',   '');
	jgSetAstro('Star',0.14, 0.2,0,0,0,6, 0,'Deneb','A Cyg', 'Y(12/5)','20090105');
	jgSetAstro('Star',0.16, 0.3,0,0,0,4, 0,'Dubhe','D UMa', 'Y(56/3)','20090230');
	jgSetAstro('Star',23.95,0.3,0,0,0,12,0,'Alfar','C Her', 'X(6)',   '');
	jgSetAstro('Star',23.90,0.2,0,0,0,14,0,'Vega' ,'D Vul', '-',      '');
	jgSetAstro('Star',23.97,0.1,0,0,0,16,0,'Arctu','A Boo', '-',      '');

  jgSetAstro('DStar',0.06,-0.1,0,0,0,10,0,'Arctu2','B Boo', '-',      '');
}
function fillAtlasPage()
{ thelocation="index.php?indexAction=atlaspage&object=M+71";
  initDiv('myDiv');
  jgCanvasInit('myDiv');
  jgGridInit();
  jgGridInitScale(ra,decl,10);
  jgGridSetHotZones();
  jgGridSetAstroObjects();
  jgGridRedraw();
  jgPaint();	
  var element = document.createElement('input');
  element.setAttribute('type','button');
  element.setAttribute('value','Up');
  element.setAttribute('name','Up');
  element.setAttribute('style','position:absolute;top:22px;left:22px;width:95px;hight:30px;');
  element.setAttribute('onclick','location=\"'+thelocation+'"');
  div=document.getElementById('myDiv');
  div.appendChild(element);

  }