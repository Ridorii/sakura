{% extends 'global/master.tpl' %}

{% block title %}Error Log{% endblock %}

{% block js %}
    <script type="text/javascript">
        var backtraces = document.querySelectorAll('.backtrace');
        for (var i in backtraces) {
            backtraces[i].innerHTML = atob(backtraces[i].innerHTML);
        }
    </script>
{% endblock %}

{% block content %}
    <div class="main">
        <h1 class="page-header">Error Log <small>A log of server side errors that Sakura could catch</small></h1>
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Occurred</th>
                    <th>Revision</th>
                    <th>Type</th>
                    <th>Line</th>
                    <th>String</th>
                    <th>File</th>
                </tr>
            </thead>
            <tbody>
                {% for error in errors %}
                <tr>
                    <td>{{ error.error_id }}</td>
                    <td>{{ error.error_timestamp }}</td>
                    <td>{{ error.error_revision }}</td>
                    <td>{{ error.error_type }}</td>
                    <td>{{ error.error_line }}</td>
                    <td>{{ error.error_string }}</td>
                    <td>{{ error.error_file }}</td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
{% endblock %}
