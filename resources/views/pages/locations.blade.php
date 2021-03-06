@extends('layouts.admin')
@section('title')
    Locationübersicht
@stop
@section('table')
    <div class="container">
        <button type="button" class="btn btn-primary" onclick="renderNewInline(this)" style="float: right; margin-bottom: 10px;">Hinzufügen</button>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Addresse</th>
                <th>Staat</th>
                <th>Telefon</th>
                <th>Kapazität</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody id="tableBody">
            @foreach($results as $result)
                <tr>
                    <td class="tdID">{{$result->id}}</td>
                    <td class="name">{{$result->name}}</td>
                    <td class="address">{{$result->address}}</td>
                    <td class="state">{{$result->state}}</td>
                    <td class="telephone">{{$result->telephone}}</td>
                    <td class="maxCap">{{$result->maxCap}}</td>
                    <td><button><a href="{{ url('admin/location/'.$result->id).'/offers' }}">Angebote</a></button></td>
                    <td class="editButton"><button type="put" class="btn btn-primary" onclick="renderEditInline(this)" value="{{$result->id}}">Edit</button></td>
                    <td class="deleteButton"><button type="delete" class="btn btn-primary" onclick="deleteThisRow(this)" value="{{$result->id}}">Delete</button></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@stop
<script type="text/javascript">
    function renderNewInline(element) {
        var nameInput = '<td><input name="name" type="text" value=""></td>';
        var addressInput = '<td><input name ="address" type="text" value=""></td>';
        var stateInput = '<td><input name="state" type="text" value=""></td>';
        var telephoneInput = '<td><input name="telephone" type="text" value="" required></td>';
        var maxCapInput = '<td><input name="maxCap" type="number" value="" required></td>';
        var submitButtonInput = '<td><button type="POST" class="btn btn-primary" onclick="postRow(this)">Submit</button></td>';
        var htmlForm = nameInput + addressInput + stateInput + telephoneInput + maxCapInput + submitButtonInput;
        $(element).next().prepend('<tr><td></td>'+htmlForm+'</tr>');
    }

    function renderEditInline(element){
        var superNode = $(element).parent().parent();
        var idTd = '<td><input name="tdID" type="text" value="' + superNode.find('.tdID').text() + '" disabled></td>'
        var nameInput = '<td><input name="name" type="text" value="' + superNode.find('.name').text() + '" required></td>';
        var addressInput = '<td><input name ="address" type="text" value=' + superNode.find('.address').text() + ' required></td>';
        var stateInput = '<td><input name ="state" type="text" value=' + superNode.find('.state').text() + ' required></td>';
        var telephoneInput = '<td><input name="telephone" type="text" value=' + superNode.find('.telephone').text() + ' required></td>';
        var maxcapInput = '<td><input name="maxCap" type="number" value=' + superNode.find('.maxCap').text() + ' required></td>';
        var submitButtonInput = '<td><button type="PUT" class="btn btn-primary" onclick="postRow(this)">Submit</button></td>';
        var htmlForm = idTd + nameInput + addressInput + stateInput + telephoneInput + maxcapInput + submitButtonInput;
        superNode.html(htmlForm);
    }
    function postRow(element) {
        var passThisOn = element;
        var valid = true;
        $(element).parent().parent().children().each(function() {
            if ($(this).children().is('input')) {
                if($(this).find('input').val() == "") {
                    valid = false;
                }
            }
        });
        if(valid) {
            ajaxCall(passThisOn);
        } else {
            alert('Bitte füllen Sie alle Felder aus!');
        }
    }
    function ajaxCall(element) {
        //Reusability across the board; Difference between PUT and POST
        var keys = ['tdID', 'name', 'address', 'state', 'telephone', 'maxCap'];
        var url;
        var posttype = $(element).attr('type');
        var postArray = {};
        //For each input field...
        $(element).parent().parent().children().each(function() {
            if ($(this).children().is('input')) {
                //Selects input field
                var selectedInput = $(this).find('input');
                var selectedInputName = selectedInput.attr('name');
                postArray[selectedInputName] = selectedInput.val();
            }
        });
        //Set up url
        if(posttype == "PUT") {
            console.log("Going with PUT");
            url =  "../location/" + postArray['school_Id']
        } else if (posttype == "POST") {
            console.log("Going with POST");
            url = "../location/";
        }
        //Set CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        //send ajax
        $.ajax({
            type: posttype,
            url: url,
            data: postArray,
            success: function(data) {
                var row = prepareRow(data.id, postArray, keys);
                if (posttype == "POST") {
                    $("#tableBody tr:last").after('<tr>'+row+'</tr>');
                    $(element).parent().parent().remove();
                } else if (posttype == "PUT") {
                    $(element).parent().parent().html(row);
                }
            },
            error: function(data) {console.log(data);
                console.log("error")},
            dataType: "JSON"
        });
    }
    function deleteRow(element){
        //Set CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        //send ajax
        $.ajax({
            type: "DELETE",
            url: "../../../location/" + $(element).val(),
            success: function(data) {
                $(element).parent().parent().remove();
            },
            error: function(data) { alert('Konnte nicht ausgeführt werden. Fehler am Server!');
                console.log("error")}
        });
    }

    function deleteThisRow(element) {
        var r = confirm("Wollen sie diesen Eintrag wirklich löschen?!");
        if (r == true) {
            deleteRow(element);
        }
    }
    function prepareRow(id, values, keys) {
        values['tdID'] = id;
        var row = "";
        keys.forEach( function(item, index) {
            row += '<td class="' + item + '">' + values[item] + '</td>'
        });
        var linkToOffers = '<td><button  class="btn" ><a href="' + window.location.host + '/admin/location/' + values['tdID'] +'/offers/">Angebote</a></button></td>';
        var edit = '<td class="editButton"><button type="put" onclick="renderEditInline(this)" value="'+id+'" class="btn btn-primary">Edit</button></td>';
        var del = '<td class="deleteButton"><button type="delete" class="btn btn-primary" onclick="deleteThisRow(this)" value="'+id+'">Delete</button></td>';
        row += linkToOffers + edit + del;
        return row;
    }
</script>