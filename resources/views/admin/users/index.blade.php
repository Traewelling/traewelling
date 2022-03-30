@extends('admin.layout')

@section('title', 'Users')

@section('content')
    <div class="row">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Users</h5>


                </div>
            </div>
    </div>
    <div class="row">
        <div class="card">
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <td class="text-center">#</td>
                            <td>Username</td>
                            <td>Displayname</td>
                            <td class="text-center">Telefonpin</td>
                            <td>Registrierung</td>
                            <td>DSGVO-Accept</td>
                            <td class="text-center">Twitter</td>
                            <td class="text-center">Mastodon</td>
                            <td class="text-center">Aktionen</td>
                        </tr>
                    </thead>
                    <tr>
                        <td class="text-center">
                            <code>12345</code>
                        </td>
                        <td>
                            <a href="#">
                                @Gertrud123
                            </a>
                        </td>
                        <td>Gertrud von Träwelling</td>
                        <td class="text-center"><code>133756</code></td>
                        <td>2013-01-01T17:35:02</td>
                        <td>2013-01-01T17:36:02</td>
                        <td class="text-center">
                            <a href="#" class="btn btn-small btn-info">
                                <i class="fab fa-twitter"></i>
                            </a>
                        </td>
                        <td class="text-center">
                            <a href="#" class="btn btn-small btn-info">
                                <i class="fab fa-mastodon"></i>
                            </a>
                        </td>
                        <td class="text-center">
                            <a href="#" class="btn btn-small btn-success" title="Neuen Checkin erstellen">
                                <i class="fas fa-plus-circle"></i>
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection
