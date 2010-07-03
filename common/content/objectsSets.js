function generate()
{ i=0;
  while(theobject=document.getElementById('R'+i))
  { var load = window.open('objectsSet.pdf?theobject='+document.getElementById('R'+i).innerHTML+'&theSet='+document.getElementById('R'+i+'Dfov').value,document.getElementById('R'+i).innerHTML);
    alert('Click ok when '+document.getElementById('R'+i).innerHTML+'is finished.');
    i++;
  }
}