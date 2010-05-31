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
  alert('Done');
}
