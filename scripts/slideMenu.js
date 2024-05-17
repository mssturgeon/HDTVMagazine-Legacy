	slideOutMenu.Registry = []
	slideOutMenu.aniLen = 250
	slideOutMenu.hideDelay = 100
	slideOutMenu.minCPUResolution = 10
	slideOutMenu.checkselect = 1

	function slideOutMenu(id, dir, left, top, width, height)
	{
		this.ie = document.all ? 1 : 0
		this.ns4 = document.layers ? 1 : 0
		this.dom = document.getElementById ? 1 : 0
		if (this.ie || this.ns4 || this.dom)
		{
			this.id = id
			this.dir = dir
			this.orientation = dir == "left" || dir == "right" ? "h" : "v"
			this.dirType = dir == "right" || dir == "down" ? "-" : "+"
			this.dim = this.orientation == "h" ? width : height
			this.hideTimer = false
			this.aniTimer = false
			this.open = false
			this.over = false
			this.startTime = 0
			this.gRef = "slideOutMenu_"+id
			eval(this.gRef+"=this")
			slideOutMenu.Registry[id] = this
			var d = document
			d.write('<style type="text/css">')
			d.write('#' + this.id + 'Container { visibility:hidden; ')
			d.write('left:' + left + 'px; ')
			d.write('top:' + top + 'px; ')
			d.write('overflow:hidden; }')
			d.write('#' + this.id + 'Container, #' + this.id + 'Content { position:absolute; ')
			d.write('width:' + width + 'px; ')
			d.write('height:' + height + 'px; ')
			d.write('clip:rect(0 ' + width + ' ' + height + ' 0); ')
			d.write('}')

			d.write('#' + this.id + 'Container, #' + this.id + 'Shim { position:absolute; ')
			d.write('width:' + width + 'px; ')
			d.write('height:' + height + 'px; ')
			d.write('}')

			d.write('</style>')
			this.load()
		}
	}



	slideOutMenu.prototype.load = function()
	{
		var d = document
		var lyrId1 = this.id + "Container"
		var lyrId2 = this.id + "Content"
		var obj1 = this.dom ? d.getElementById(lyrId1) : this.ie ? d.all[lyrId1] : d.layers[lyrId1]
		if (obj1) var obj2 = this.ns4 ? obj1.layers[lyrId2] : this.ie ? d.all[lyrId2] : d.getElementById(lyrId2)
		var temp
		if (!obj1 || !obj2) window.setTimeout(this.gRef + ".load()", 100)
		else {
			this.container = obj1
			this.menu = obj2
			this.style = this.ns4 ? this.menu : this.menu.style
			this.homePos = eval("0" + this.dirType + this.dim)
			this.outPos = 0
			this.accelConst = (this.outPos - this.homePos) / slideOutMenu.aniLen / slideOutMenu.aniLen
			if (this.ns4) this.menu.captureEvents(Event.MOUSEOVER | Event.MOUSEOUT);
			this.menu.onmouseover = new Function("slideOutMenu.showMenu('" + this.id + "')")
			this.menu.onmouseout = new Function("slideOutMenu.hideMenu('" + this.id + "')")
			this.endSlide()
		}
	}

	slideOutMenu.showMenu = function(id)
	{
		var reg = slideOutMenu.Registry
		var obj = slideOutMenu.Registry[id]
		if (obj.container)
		{
			obj.over = true
			for (menu in reg) //if (id != menu) (obj.hideTimer)
			if (obj.hideTimer) { reg[id].hideTimer = window.clearTimeout(reg[id].hideTimer) }
			if (!obj.open && !obj.aniTimer) reg[id].startSlide(true)
		}
	}

	slideOutMenu.hideMenu = function(id)
	{
		var obj = slideOutMenu.Registry[id]
		if (obj.container)
		{
			if (obj.hideTimer) window.clearTimeout(obj.hideTimer)
			obj.hideTimer = window.setTimeout("slideOutMenu.hide('" + id + "')", slideOutMenu.hideDelay);
		}
	}

	slideOutMenu.hide = function(id)
	{
		var obj = slideOutMenu.Registry[id]

		obj.over = false
		if (obj.hideTimer) window.clearTimeout(obj.hideTimer)
		obj.hideTimer = 0
		if (obj.open && !obj.aniTimer) obj.startSlide(false)
	}

	slideOutMenu.prototype.startSlide = function(open)
	{
		this[open ? "onactivate" : "ondeactivate"]()
		this.open = open
		if (open) this.setVisibility(true)
		this.startTime = (new Date()).getTime()
		this.aniTimer = window.setInterval(this.gRef + ".slide()", slideOutMenu.minCPUResolution)
	}

	slideOutMenu.prototype.slide = function()
	{
		var elapsed = (new Date()).getTime() - this.startTime
		if (elapsed > slideOutMenu.aniLen) this.endSlide()
		else {
			var d = Math.round(Math.pow(slideOutMenu.aniLen-elapsed, 2) * this.accelConst)
			if (this.open && this.dirType == "-") d = -d
			else if (this.open && this.dirType == "+") d = -d
			else if (!this.open && this.dirType == "-") d = -this.dim + d
			else d = this.dim + d
			this.moveTo(d)
		}
	}

	slideOutMenu.prototype.endSlide = function()
	{
		this.aniTimer = window.clearTimeout(this.aniTimer)
		this.moveTo(this.open ? this.outPos : this.homePos)
		if (!this.open) this.setVisibility(false)
		if ((this.open && !this.over) || (!this.open && this.over))
		{
			this.startSlide(this.over)
		}
	}

	slideOutMenu.prototype.setVisibility = function(bShow)
	{
		var s = this.ns4 ? this.container : this.container.style
		s.visibility = bShow ? "visible" : "hidden"
	}

	slideOutMenu.prototype.moveTo = function(p)
	{
		this.style[this.orientation == "h" ? "left" : "top"] = p
	}

	slideOutMenu.prototype.getPos = function(c)
	{
		return parseInt(this.style[c])
	}

	slideOutMenu.prototype.onactivate = function() { }

	slideOutMenu.prototype.ondeactivate = function() { }