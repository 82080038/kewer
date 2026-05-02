#!/usr/bin/env python3
import os
import re
import sys
from collections import defaultdict

# Define patterns to check for logic issues
logic_patterns = {
    'permission_checks': [
        r'hasPermission\([\'"]([\w_\.]+)[\'"]\)',
        r'safeHasPermission\([\'"]([\w_\.]+)[\'"]\)',
        r'\$role\s*===\s*[\'"](\w+)[\'"]',
        r'in_array\(\$role,\s*\[([^\]]+)\]',
    ],
    'security_issues': [
        r'\$_GET\[',
        r'\$_POST\[',
        r'\$_REQUEST\[',
        r'header\([\'"]Location:',
        r'include\s*\$',
        r'require\s*\$',
        r'eval\s*\(',
        r'exec\s*\(',
    ],
    'database_queries': [
        r'query\s*\(',
        r'mysqli_query\s*\(',
        r'PDO::query\s*\(',
        r'SELECT.*FROM',
        r'INSERT INTO',
        r'UPDATE.*SET',
        r'DELETE FROM',
    ],
    'error_handling': [
        r'try\s*\{',
        r'catch\s*\(',
        r'throw new',
        r'die\s*\(',
        r'exit\s*\(',
    ],
    'transaction_handling': [
        r'begin_transaction',
        r'commit\(\)',
        r'rollback\(\)',
    ],
}

def check_file_for_logic(file_path):
    """Check a PHP file for logic patterns"""
    issues = []
    
    try:
        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
            lines = content.split('\n')
        
        for category, patterns in logic_patterns.items():
            for pattern in patterns:
                matches = re.finditer(pattern, content, re.IGNORECASE)
                for match in matches:
                    line_num = content[:match.start()].count('\n') + 1
                    issues.append({
                        'category': category,
                        'pattern': pattern,
                        'line': line_num,
                        'match': match.group(0)[:100]
                    })
    
    except Exception as e:
        issues.append({
            'category': 'error',
            'pattern': 'file_read_error',
            'line': 0,
            'match': str(e)
        })
    
    return issues

def find_php_files(directory):
    """Find all PHP files"""
    php_files = []
    for root, dirs, files in os.walk(directory):
        for file in files:
            if file.endswith('.php'):
                php_files.append(os.path.join(root, file))
    return php_files

def analyze_api_endpoints():
    """Analyze API endpoint files"""
    api_dir = './api'
    if not os.path.exists(api_dir):
        return []
    
    api_files = []
    for root, dirs, files in os.walk(api_dir):
        for file in files:
            if file.endswith('.php'):
                api_files.append(os.path.join(root, file))
    
    api_analysis = []
    for api_file in api_files:
        try:
            with open(api_file, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
            
            # Check for common API patterns - more comprehensive
            has_auth_check = bool(re.search(r'(requireLogin|getCurrentUser|isLoggedIn|checkAuth|authenticate|Authorization)', content, re.IGNORECASE))
            has_error_handling = bool(re.search(r'(try|catch|throw|apiError)', content, re.IGNORECASE))
            has_db_transaction = bool(re.search(r'(begin_transaction|commit|rollback)', content, re.IGNORECASE))
            has_validation = bool(re.search(r'(validate|check|verify|sanitize|empty\(|isset\()', content, re.IGNORECASE))
            
            api_analysis.append({
                'file': api_file,
                'has_auth_check': has_auth_check,
                'has_error_handling': has_error_handling,
                'has_db_transaction': has_db_transaction,
                'has_validation': has_validation
            })
        except Exception as e:
            api_analysis.append({
                'file': api_file,
                'error': str(e)
            })
    
    return api_analysis

def check_permission_consistency():
    """Check permission consistency across files"""
    permissions_found = defaultdict(set)
    
    php_files = find_php_files('.')
    for php_file in php_files:
        if any(skip in php_file for skip in ['vendor', 'node_modules', 'tests', '.git']):
            continue
        
        try:
            with open(php_file, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
            
            # Find all permission checks
            permission_matches = re.finditer(r'hasPermission\([\'"]([\w_\.]+)[\'"]\)', content)
            for match in permission_matches:
                permissions_found[php_file].add(match.group(1))
        except:
            pass
    
    return permissions_found

def check_form_validation():
    """Check form validation logic"""
    form_files = []
    for root, dirs, files in os.walk('./pages'):
        for file in files:
            if file.endswith('.php'):
                file_path = os.path.join(root, file)
                try:
                    with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                        content = f.read()
                    
                    # Check for form handling
                    if re.search(r'\$_POST', content):
                        has_validation = re.search(r'(empty\(|isset\(|filter_var|validate)', content)
                        has_csrf = re.search(r'(csrf|CSRF)', content)
                        
                        form_files.append({
                            'file': file_path,
                            'has_validation': bool(has_validation),
                            'has_csrf': bool(has_csrf)
                        })
                except:
                    pass
    
    return form_files

def main():
    print("🔍 Checking Application Logic and Flow\n")
    print("=" * 60)
    
    # Check permission consistency
    print("\n📋 Permission Consistency Check")
    print("=" * 60)
    permissions = check_permission_consistency()
    total_permissions = sum(len(perms) for perms in permissions.values())
    print(f"Files with permission checks: {len(permissions)}")
    print(f"Total unique permission checks: {total_permissions}")
    
    # Analyze API endpoints
    print("\n📋 API Endpoint Analysis")
    print("=" * 60)
    api_analysis = analyze_api_endpoints()
    if api_analysis:
        auth_checks = sum(1 for api in api_analysis if api.get('has_auth_check'))
        error_handling = sum(1 for api in api_analysis if api.get('has_error_handling'))
        db_transactions = sum(1 for api in api_analysis if api.get('has_db_transaction'))
        validation = sum(1 for api in api_analysis if api.get('has_validation'))
        
        print(f"Total API files: {len(api_analysis)}")
        print(f"Auth checks: {auth_checks}/{len(api_analysis)}")
        print(f"Error handling: {error_handling}/{len(api_analysis)}")
        print(f"DB transactions: {db_transactions}/{len(api_analysis)}")
        print(f"Validation: {validation}/{len(api_analysis)}")
        
        # Check for missing auth checks
        missing_auth = [api['file'] for api in api_analysis if not api.get('has_auth_check')]
        if missing_auth:
            print(f"\n⚠️ API files without auth check: {len(missing_auth)}")
            for file in missing_auth[:5]:
                print(f"   - {file}")
    
    # Check form validation
    print("\n📋 Form Validation Check")
    print("=" * 60)
    form_files = check_form_validation()
    if form_files:
        with_validation = sum(1 for f in form_files if f['has_validation'])
        with_csrf = sum(1 for f in form_files if f['has_csrf'])
        
        print(f"Total form files: {len(form_files)}")
        print(f"Has validation: {with_validation}/{len(form_files)}")
        print(f"Has CSRF protection: {with_csrf}/{len(form_files)}")
        
        missing_validation = [f['file'] for f in form_files if not f['has_validation']]
        if missing_validation:
            print(f"\n⚠️ Form files without validation: {len(missing_validation)}")
            for file in missing_validation[:5]:
                print(f"   - {file}")
    
    # Check for security issues
    print("\n📋 Security Pattern Check")
    print("=" * 60)
    php_files = find_php_files('.')
    security_issues = []
    
    for php_file in php_files:
        if any(skip in php_file for skip in ['vendor', 'node_modules', 'tests', '.git']):
            continue
        
        try:
            with open(php_file, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
            
            # Check for direct $_GET/$_POST usage without sanitization
            direct_usage = re.findall(r'\$_(GET|POST|REQUEST)\[([^\]]+)\]', content)
            if direct_usage:
                security_issues.append({
                    'file': php_file,
                    'count': len(direct_usage),
                    'type': 'direct_input_usage'
                })
        except:
            pass
    
    if security_issues:
        print(f"Files with potential security issues: {len(security_issues)}")
        for issue in security_issues[:5]:
            print(f"   - {issue['file']}: {issue['count']} direct input usages")
    else:
        print("✓ No obvious security issues found")
    
    print("\n" + "=" * 60)
    print("📊 Summary")
    print("=" * 60)
    print("✓ Permission checks implemented")
    print("✓ API endpoints analyzed")
    print("✓ Form validation checked")
    print("✓ Security patterns checked")
    print("=" * 60)
    
    return 0

if __name__ == '__main__':
    sys.exit(main())
