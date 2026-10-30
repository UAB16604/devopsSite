pipeline {
    agent any
    triggers { githubPush() }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
                sh 'ls -la'
            }
        }

        stage('Validate Manifests') {
            steps {
                sh 'kubectl apply --dry-run=client -f k8s/tekken-app.yaml'
            }
        }

        stage('Build Backend Image') {
            steps {
                sh 'minikube image build -t tekken-backend:latest backend'
            }
        }

        stage('Build Frontend Image') {
            steps {
                sh 'minikube image build -t tekken-frontend:latest frontend'
            }
        }

        stage('Deploy to Kubernetes') {
            steps {
                withCredentials([
                    string(credentialsId: 'tekken-db-password', variable: 'DB_PASSWORD'),
                    string(credentialsId: 'tekken-db-root-password', variable: 'DB_ROOT_PASSWORD')
                ]) {
                    sh '''
                        kubectl create secret generic tekken-db-secret \
                          --from-literal=DB_PASSWORD="$DB_PASSWORD" \
                          --from-literal=DB_ROOT_PASSWORD="$DB_ROOT_PASSWORD" \
                          --dry-run=client -o yaml | kubectl apply -f -
                        kubectl create configmap tekken-db-init --from-file=database/ \
                          --dry-run=client -o yaml | kubectl apply -f -
                        kubectl apply -f k8s/tekken-app.yaml
                        kubectl rollout restart deployment/tekken-backend deployment/tekken-frontend
                    '''
                }
            }
        }

        stage('Wait for Rollout') {
            steps {
                sh '''
                    kubectl rollout status deployment/tekken-db --timeout=300s
                    kubectl rollout status deployment/tekken-backend --timeout=300s
                    kubectl rollout status deployment/tekken-frontend --timeout=300s
                '''
            }
        }

        stage('Verify') {
            steps {
                sh '''
                    kubectl get pods,svc
                    URL="http://$(minikube ip):30080/backend/players.php"
                    for i in $(seq 1 15); do
                        if curl -sf --max-time 10 "$URL" | grep -q gamer_tag; then
                            echo "Check passed: frontend, backend and database all work."; exit 0
                        fi
                        echo "Attempt $i failed, retrying in 10s..."; sleep 10
                    done
                    echo "Check failed"; exit 1
                '''
            }
        }
    }

    post {
        success { echo 'Tekken tournament deployment completed.' }
        failure { echo 'Deployment failed. Check the console output.' }
    }
}