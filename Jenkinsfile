pipeline {
    agent any
    stages {
        stage('Build frontend') {
            steps {
                dir('${workspace}/frontend') {
                    script {
                        def frontend = docker.build("journeyplanner/frontend:latest")
                    }
                }
            }
        }
        stage('Build backend') {
            steps {
                dir('${workspace}/backend') {
                    script {
                        def backend = docker.build("journeyplanner/backend:latest")
                    }
                }
            }
        }
    }
}
