function addProductRow()
{
    var table = document.getElementById('quickproductcreation_table');
    var row = document.createElement("TR");
    var id = table.tBodies[0].rows.length + 1;
    row.id = 'tr_' + id;

    for(var i = 0;i< rowTemplate.length;i++)
    {
       var td = document.createElement("TD")
       var html  = rowTemplate[i];
       html = html.replace('{id}',id);
       html = html.replace('{id}',id); //code is twice intentionnaly, todo : code this properly
       td.innerHTML = html;
       row.appendChild(td);
    }

    table.tBodies[0].appendChild(row);
}

function deleteRow(id)
{
    var trId = 'tr_' + id;
    var tr = document.getElementById(trId);
    if (tr)
        tr.style.display = 'none';
    
}