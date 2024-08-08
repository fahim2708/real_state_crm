<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bootstrap Vertical Form Layout</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>




<div class="container" style="width: 500px; margin-left:200px">


    <div class="m-4">
        <form action="{{ route('checkbox.store') }}" method="post">
            <div class="mb-3">
                <label class="form-label" for="inputEmail">Name</label>
                <input type="text" class="form-control" name="name" id="inputEmail" placeholder="Name" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="inputPassword">Email</label>
                <input type="email" class="form-control" name="email" id="inputPassword" placeholder="Email" required>
            </div>

            <div class="col-12">
                <label class="form-label" for="inputPassword">Check Item</label>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="checkbox[]" value="Music" id="checkMusic">
                    <label class="form-check-label" for="checkMusic">Music</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="checkbox[]" value="Travel" id="checkTravel">
                    <label class="form-check-label" for="checkTravel">Travel</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="checkbox[]" value="Reading" id="checkReading">
                    <label class="form-check-label" for="checkReading">Reading</label>
                </div>
            </div> <br>

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

</div>





<div class="container">
    <h2>View Data</h2>
    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Checkbox</th>
        </tr>
      </thead>
      <tbody>

@foreach ($data as $datas)
<tr>
  <td>{{$datas->name}}</td>
  <td>{{$datas->email}}</td>
  <td>{{$datas->checkbox}}</td>
</tr>

@endforeach



      </tbody>
    </table>
  </div>






  <a href="">Update Is active</a>




</body>
</html>
