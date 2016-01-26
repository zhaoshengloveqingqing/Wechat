
function CS() {
    this.CFD = "不限";
    this.CSD = "不限";
    this.ShowT = 0;
    
    this.CFData = [];
    this.CSData = [];
    this.CAData ='';

    var CLIST = arguments[4];
    if (this.ShowT)
        CLIST = this.CFD + "$" + this.CSD + "#" + CLIST;
    this.CAData = CLIST.split("#");
    for (var i = 0; i < this.CAData.length; i++) {
        parts = this.CAData[i].split("$");
        this.CFData[i] = parts[0];
        this.CSData[i] = parts[1].split(",")
    }
     
    var self = this;
    this.SelF = document.getElementById(arguments[0]);
    this.SelS = document.getElementById(arguments[1]);
    this.DefF = arguments[2]; 
    this.DefS = arguments[3];
    this.SelF.CS = this;
    this.SelS.CS = this;
    this.SelF.onchange = function () {
        CS.SetS(self)
    };
     CS.SetF(this)
};
CS.SetF = function (self) {
    for (var i = 0; i < self.CFData.length; i++) {
        var title, value;
        title = self.CFData[i].split("-")[0];
        value = self.CFData[i].split("-")[1];
        if (title == self.CFD) { value = "" }
        self.SelF.options.add(new Option(title, value));
        if (self.DefF == value) { self.SelF[i].selected = true }
    }
    CS.SetS(self)
};
CS.SetS = function (self) {
    var fi = self.SelF.selectedIndex;
    var slist = self.CSData[fi];
    self.SelS.length = 0;
    if (self.SelF.value != "" && self.ShowT) {
        self.SelS.options.add(new Option(self.CSD, ""))
    }
    for (var i = 0; i < slist.length; i++) {
        var title, value;
        title = slist[i].split("-")[0];
        value = slist[i].split("-")[1];
        if (title == self.CSD) { value = "" }
        self.SelS.options.add(new Option(title, value));
        if (self.DefS == value) {
            self.SelS[self.SelF.value == "" ? i + 1 : i].selected = true
        }
    }
}