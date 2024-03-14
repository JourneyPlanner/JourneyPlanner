pipeline {
    agent any
    stages {
        stage('Build frontend') {
            steps {
                dir('/frontend') {
                    script {
                        def frontend = docker.build("journeyplanner/frontend:latest")
                    }
                }
            }
        }
        stage('Build backend') {
            steps {
                dir('/backend') {
                    script {
                        def backend = docker.build("journeyplanner/backend:latest")
                    }
                }
            }
        }
    }
}
