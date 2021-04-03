function graf(data) {
    console.log(data)
    var dps = [];
   // dps = [{y: 1, label: 10}, {y: 2, label: 10}, {y: 3, label: 10}, {y: 4, label: 10}, {y: 5, label: 10}];
   for (var i=0;i<data.length;i++){
        var x = new Object();
        x.y = parseInt(data[i]);
        x.label = "Prednaska "+(i+1);
        console.log(x);
       dps.push(x);
    }
   console.log(dps);

    var chart = new CanvasJS.Chart("graf", {
        animationEnabled: true,
        title: {
            text: ""
        },
        data: [{
            type: "pie",
            startAngle: 225,
            yValueFormatString: "##0\"\"",
            indexLabel: "{label}",
            dataPoints: dps
        }],

    });
    chart.render();

}

$(document).ready(function() {
    $('#table').DataTable( {
        columnDefs: [ {
            targets: [ 4 ],
            orderData: [4, 2 ]
        }],
        "searching": false,
        "paging": false,
        "lengthChange": false,
        "info": false,
        "order": [[1,'asc']],
    } );
} );
