function clearFields()
{ document.getElementById('seen').selectedIndex=0;
  document.getElementById('catalog').selectedIndex=0;
  document.getElementById('catNumber').value='';
  document.getElementById('atlas').selectedIndex=0;
  document.getElementById('atlasPageNumber').value='';
  document.getElementById('con').selectedIndex=0;
  document.getElementById('conto').selectedIndex=0;
  document.getElementById('type').selectedIndex=0;
  document.getElementById('minDeclDegrees').value='';
  document.getElementById('minDeclMinutes').value='';
  document.getElementById('minDeclSeconds').value='';
  document.getElementById('maxDeclDegrees').value='';
  document.getElementById('maxDeclMinutes').value='';
  document.getElementById('maxDeclSeconds').value='';
  document.getElementById('minRAHours').value='';
  document.getElementById('minRAMinutes').value='';
  document.getElementById('minRASeconds').value='';
  document.getElementById('maxRAHours').value='';
  document.getElementById('maxRAMinutes').value='';
  document.getElementById('maxRASeconds').value='';
  document.getElementById('maxMag').value='';
  document.getElementById('minMag').value='';
  document.getElementById('maxSB').value='';
  document.getElementById('minSB').value='';
  document.getElementById('minSize').value='';
  document.getElementById('size_min_units').selectedIndex=0;
  document.getElementById('maxSize').value='';
  document.getElementById('size_max_units').selectedIndex=0;
  document.getElementById('minContrast').value='';
  document.getElementById('maxContrast').value='';
  document.getElementById('inList').selectedIndex=0;
  document.getElementById('descriptioncontains').value='';
//document.getElementById('notInList').selectedIndex=0;
  var temp=document.getElementById("temp").value;
  while((pos=temp.indexOf('/'))>=0)
  { document.getElementById(temp.substr(0,pos)).checked='';
    temp=temp.substr(pos+1);
  }
  document.getElementById('excludeexceptseen').checked='';
}
