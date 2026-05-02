#!/usr/bin/env python3
import urllib.request
import urllib.error
from html.parser import HTMLParser
import sys
import os
import subprocess
import re
import json

# Base URL
BASE_URL = "http://localhost/kewer"

# Define all roles and their pages
role_pages = {
    'superadmin': [
        {'name': 'Dashboard', 'url': f'{BASE_URL}/dashboard.php'},
        {'name': 'Persetujuan Bos', 'url': f'{BASE_URL}/pages/superadmin/bos_approvals.php'},
        {'name': 'Users', 'url': f'{BASE_URL}/pages/users/index.php'},
        {'name': 'Cabang', 'url': f'{BASE_URL}/pages/cabang/index.php'},
        {'name': 'Audit Trail', 'url': f'{BASE_URL}/pages/audit/index.php'},
        {'name': 'Permissions', 'url': f'{BASE_URL}/pages/permissions/index.php'},
    ],
    'bos': [
        {'name': 'Dashboard', 'url': f'{BASE_URL}/dashboard.php'},
        {'name': 'Nasabah', 'url': f'{BASE_URL}/pages/nasabah/index.php'},
        {'name': 'Pinjaman', 'url': f'{BASE_URL}/pages/pinjaman/index.php'},
        {'name': 'Angsuran', 'url': f'{BASE_URL}/pages/angsuran/index.php'},
        {'name': 'Cabang', 'url': f'{BASE_URL}/pages/cabang/index.php'},
        {'name': 'Petugas', 'url': f'{BASE_URL}/pages/petugas/index.php'},
        {'name': 'Delegasi Permission', 'url': f'{BASE_URL}/pages/bos/delegated_permissions.php'},
    ],
    'manager': [
        {'name': 'Dashboard', 'url': f'{BASE_URL}/dashboard.php'},
        {'name': 'Nasabah', 'url': f'{BASE_URL}/pages/nasabah/index.php'},
        {'name': 'Pinjaman', 'url': f'{BASE_URL}/pages/pinjaman/index.php'},
        {'name': 'Angsuran', 'url': f'{BASE_URL}/pages/angsuran/index.php'},
        {'name': 'Petugas', 'url': f'{BASE_URL}/pages/petugas/index.php'},
    ],
    'admin': [
        {'name': 'Dashboard', 'url': f'{BASE_URL}/dashboard.php'},
        {'name': 'Nasabah', 'url': f'{BASE_URL}/pages/nasabah/index.php'},
        {'name': 'Pinjaman', 'url': f'{BASE_URL}/pages/pinjaman/index.php'},
        {'name': 'Angsuran', 'url': f'{BASE_URL}/pages/angsuran/index.php'},
    ],
    'petugas': [
        {'name': 'Dashboard', 'url': f'{BASE_URL}/dashboard.php'},
        {'name': 'Nasabah', 'url': f'{BASE_URL}/pages/nasabah/index.php'},
        {'name': 'Pinjaman', 'url': f'{BASE_URL}/pages/pinjaman/index.php'},
        {'name': 'Aktivitas Lapangan', 'url': f'{BASE_URL}/pages/field_activities/index.php'},
    ],
    'karyawan': [
        {'name': 'Dashboard', 'url': f'{BASE_URL}/dashboard.php'},
        {'name': 'Pinjaman', 'url': f'{BASE_URL}/pages/pinjaman/index.php'},
    ]
}

class HTMLChecker(HTMLParser):
    def __init__(self):
        super().__init__()
        self.has_navbar = False
        self.has_sidebar = False
        self.has_main = False
    
    def handle_starttag(self, tag, attrs):
        attrs_dict = dict(attrs)
        if tag == 'nav':
            class_attr = attrs_dict.get('class', '')
            if 'navbar' in class_attr:
                self.has_navbar = True
            if 'sidebar' in class_attr:
                self.has_sidebar = True
        elif tag == 'main':
            self.has_main = True

def check_php_syntax(file_path):
    """Check PHP syntax using php -l command"""
    try:
        # Use full path to PHP on Windows
        php_path = r'C:\xampp\php\php.exe'
        if not os.path.exists(php_path):
            php_path = 'php'  # Fallback to system PATH
        
        result = subprocess.run(
            [php_path, '-l', file_path],
            capture_output=True,
            text=True,
            timeout=5
        )
        if result.returncode == 0:
            return None
        else:
            return result.stderr.strip() or result.stdout.strip()
    except Exception as e:
        return f"Error checking syntax: {str(e)}"

def find_php_files(directory):
    """Find all PHP files in directory recursively"""
    php_files = []
    for root, dirs, files in os.walk(directory):
        for file in files:
            if file.endswith('.php'):
                php_files.append(os.path.join(root, file))
    return php_files

def check_all_php_files():
    """Check all PHP files for syntax errors"""
    print("🔍 Checking All PHP Files for Syntax Errors\n")
    print("=" * 60)
    
    php_files = find_php_files('.')
    print(f"Found {len(php_files)} PHP files\n")
    
    errors = []
    checked = 0
    
    for php_file in php_files:
        # Skip vendor, node_modules, tests directories
        if any(skip in php_file for skip in ['vendor', 'node_modules', 'tests', '.git']):
            continue
        
        syntax_error = check_php_syntax(php_file)
        checked += 1
        
        if syntax_error:
            errors.append({
                'file': php_file,
                'error': syntax_error
            })
            print(f"❌ {php_file}")
            print(f"   Error: {syntax_error}")
    
    print("\n" + "=" * 60)
    print(f"📊 PHP Syntax Check Summary")
    print("=" * 60)
    print(f"Total Files Checked: {checked}")
    print(f"✓ No Errors: {checked - len(errors)}")
    print(f"❌ Errors: {len(errors)}")
    
    if errors:
        print("\n❌ Files with Errors:")
        for err in errors:
            print(f"   - {err['file']}")
            print(f"     Error: {err['error']}")
    
    print("=" * 60)
    
    return errors

def check_role_pages(role_name, pages):
    """Check pages for a specific role"""
    print(f"\n🔍 Checking {role_name.upper()} Role Pages\n")
    print("=" * 60)
    
    results = []
    
    for page in pages:
        result = check_page(page)
        result['role'] = role_name
        results.append(result)
        
        print(f"\n📋 {result['name']}")
        print(f"   URL: {result['url']}")
        print(f"   Status: {result['status']}")
        print(f"   Navbar: {'✓' if result['has_navbar'] else '✗'}")
        print(f"   Sidebar: {'✓' if result['has_sidebar'] else '✗'}")
        print(f"   Main Content: {'✓' if result['has_main'] else '✗'}")
        
        if result['error']:
            print(f"   ❌ Error: {result['error']}")
        
        if result['php_error']:
            print(f"   ⚠️ PHP Error: {result['php_error']}")
    
    print("\n" + "=" * 60)
    
    total = len(results)
    passed = sum(1 for r in results if r['status'] == 'HTTP 200' and not r['error'] and not r['php_error'])
    failed = total - passed
    
    print(f"📊 {role_name.upper()} Summary")
    print("=" * 60)
    print(f"Total Pages: {total}")
    print(f"✓ Passed: {passed}")
    print(f"✗ Failed: {failed}")
    
    if failed > 0:
        print("\n❌ Failed Pages:")
        for r in results:
            if r['error'] or r['php_error'] or r['status'] != 'HTTP 200':
                print(f"   - {r['name']}: {r['status']}")
                if r['error']:
                    print(f"     Error: {r['error']}")
                if r['php_error']:
                    print(f"     PHP Error: {r['php_error']}")
    
    print("=" * 60)
    
    return results

def check_page(page):
    try:
        req = urllib.request.Request(page['url'])
        with urllib.request.urlopen(req, timeout=10) as response:
            status_code = response.getcode()
            content = response.read().decode('utf-8', errors='ignore')
            
            if status_code != 200:
                return {
                    'name': page['name'],
                    'url': page['url'],
                    'status': f'HTTP {status_code}',
                    'error': 'Non-200 status code',
                    'has_navbar': False,
                    'has_sidebar': False,
                    'has_main': False,
                    'php_error': None
                }
            
            # Parse HTML
            parser = HTMLChecker()
            parser.feed(content)
            
            # Check for PHP errors in content
            php_error = None
            if 'Warning:' in content or 'Fatal error:' in content or 'Parse error:' in content:
                php_error = 'PHP error detected'
                # Try to extract error message
                warning_match = re.search(r'<div[^>]*style=[\'"][^\'"]*background[^>]*>(.*?)(?:Warning|Fatal error|Parse error)(.*?)</div>', content, re.DOTALL)
                if warning_match:
                    php_error = warning_match.group(0)[:200]
            
            return {
                'name': page['name'],
                'url': page['url'],
                'status': f'HTTP {status_code}',
                'error': None,
                'has_navbar': parser.has_navbar,
                'has_sidebar': parser.has_sidebar,
                'has_main': parser.has_main,
                'php_error': php_error
            }
            
    except urllib.error.URLError as e:
        return {
            'name': page['name'],
            'url': page['url'],
            'status': 'Failed',
            'error': str(e),
            'has_navbar': False,
            'has_sidebar': False,
            'has_main': False,
            'php_error': None
        }
    except Exception as e:
        return {
            'name': page['name'],
            'url': page['url'],
            'status': 'Failed',
            'error': str(e),
            'has_navbar': False,
            'has_sidebar': False,
            'has_main': False,
            'php_error': None
        }

def main():
    print("🚀 Comprehensive Application Check - All Roles\n")
    print("=" * 60)
    
    # First check PHP syntax errors
    php_errors = check_all_php_files()
    print()
    
    # Check all roles
    all_results = []
    all_errors = []
    
    for role_name, pages in role_pages.items():
        role_results = check_role_pages(role_name, pages)
        all_results.extend(role_results)
        
        # Collect errors
        for r in role_results:
            if r['error'] or r['php_error'] or r['status'] != 'HTTP 200':
                all_errors.append({
                    'role': role_name,
                    'page': r['name'],
                    'url': r['url'],
                    'status': r['status'],
                    'error': r['error'],
                    'php_error': r['php_error']
                })
    
    # Overall summary
    total_pages = sum(len(pages) for pages in role_pages.values())
    total_passed = sum(1 for r in all_results if r['status'] == 'HTTP 200' and not r['error'] and not r['php_error'])
    total_failed = total_pages - total_passed
    
    print("\n" + "=" * 60)
    print("📊 Overall Summary")
    print("=" * 60)
    print(f"PHP Syntax Errors: {len(php_errors)}")
    print(f"Total Roles Checked: {len(role_pages)}")
    print(f"Total Pages Checked: {total_pages}")
    print(f"✓ Passed: {total_passed}")
    print(f"✗ Failed: {total_failed}")
    print(f"Total Errors: {len(php_errors) + total_failed}")
    print("=" * 60)
    
    if all_errors:
        print("\n❌ All Errors by Role:")
        for err in all_errors:
            print(f"   [{err['role'].upper()}] {err['page']}")
            print(f"     URL: {err['url']}")
            print(f"     Status: {err['status']}")
            if err['error']:
                print(f"     Error: {err['error']}")
            if err['php_error']:
                print(f"     PHP Error: {err['php_error']}")
    
    # Check for layout inconsistencies
    print("\n" + "=" * 60)
    print("🎨 Layout Consistency Check")
    print("=" * 60)
    
    layout_issues = []
    for r in all_results:
        if r['status'] == 'HTTP 200':
            if not r['has_navbar']:
                layout_issues.append({
                    'role': r['role'],
                    'page': r['name'],
                    'issue': 'Missing navbar'
                })
            if not r['has_sidebar']:
                layout_issues.append({
                    'role': r['role'],
                    'page': r['name'],
                    'issue': 'Missing sidebar'
                })
            if not r['has_main']:
                layout_issues.append({
                    'role': r['role'],
                    'page': r['name'],
                    'issue': 'Missing main content'
                })
    
    if layout_issues:
        print(f"❌ Layout Issues Found: {len(layout_issues)}")
        for issue in layout_issues:
            print(f"   [{issue['role'].upper()}] {issue['page']}: {issue['issue']}")
    else:
        print("✓ No layout issues found")
    
    print("=" * 60)
    
    total_issues = len(php_errors) + len(all_errors) + len(layout_issues)
    return 0 if total_issues == 0 else 1

if __name__ == '__main__':
    sys.exit(main())
