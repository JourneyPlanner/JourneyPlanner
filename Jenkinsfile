pipeline {
    agent any
    environment {
        FRONTEND_IMAGE = 'journeyplanner/frontend:latest'
        BACKEND_IMAGE = 'journeyplanner/backend:latest'
        I18N_IMAGE = 'journeyplanner/i18n:latest'
    }
    stages {
        stage('Build Frontend') {
            steps {
                dir('frontend') {
                    script {
                        docker.build(env.FRONTEND_IMAGE)
                    }
                }
            }
        }
        stage('Check Frontend Code') {
            steps {
                dir('frontend') {
                    script {
                        sh 'npm install'
                        sh 'npx prettier . --check'
                    }
                }
            }
        }
        stage('Build Backend') {
            steps {
                dir('backend') {
                    script {
                        docker.build(env.BACKEND_IMAGE)
                    }
                }
            }
        }
        stage('Check Backend Code') {
            steps {
                dir('backend') {
                    script {
                        sh 'npm install'
                        sh 'npx prettier . --check'
                    }
                }
            }
        }
        stage('Build i18n Image') {
            steps {
                dir('docker/i18n') {
                    script {
                        docker.build(env.I18N_IMAGE)
                    }
                }
            }
        }
    }
}
