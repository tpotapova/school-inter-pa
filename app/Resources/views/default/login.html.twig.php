{% extends '::base.html.twig' %}

{% block body %}

<form action="{{( path('login')}}" method="POST">
    <label for="inputName">Username</label>
    <input type="text" name="_username" class="form-control" id="inputName" placeholder="Username">
    <label for="inputPassword">Password</label>
    <input type="password" name="_password" class="form-control" id="inputPassword" placeholder="Password">
    <button type="submit" class="btn btn-default">Login</button>
</form>

{% endblock %}